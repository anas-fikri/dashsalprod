<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\File;

class ExcelParserService
{
    /**
     * Map of Indonesian month names.
     */
    protected array $months = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember',
    ];

    /**
     * Parse the Excel serial date to YYYY-MM.
     */
    public function convertExcelDate($serial): ?string
    {
        if (is_numeric($serial)) {
            $utcDays = $serial - 25569;
            $timestamp = $utcDays * 86400;
            return date('Y-m', $timestamp);
        }
        
        if (is_string($serial) && preg_match('/^\d{4}-\d{2}$/', trim($serial))) {
            return trim($serial);
        }

        return null;
    }

    /**
     * Parse payroll Excel file.
     */
    public function parsePayroll(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('TEMP');

        if (!$sheet) {
            throw new \Exception("Sheet 'TEMP' tidak ditemukan dalam file Excel.");
        }

        $rows = $sheet->toArray();
        $header = array_shift($rows);

        $parsedData = [];
        $totalNetSalary = 0;
        $entityCounts = ['NES' => 0, 'NPA' => 0];
        $detectedPeriod = null;

        foreach ($rows as $index => $row) {
            // Check if it's an empty row or doesn't have NIK
            if (empty($row[10]) || trim($row[10]) === '') {
                continue;
            }

            $companyCode = trim($row[1] ?? '');
            $rawPeriod = $row[2] ?? null;
            $period = $this->convertExcelDate($rawPeriod);
            
            if (!$detectedPeriod && $period) {
                $detectedPeriod = $period;
            }

            $subDept = trim($row[6] ?? '');
            $structuralStatus = trim($row[9] ?? '');
            $nik = trim($row[10] ?? '');
            $nama = trim($row[11] ?? '');
            
            $basicSalary = floatval($row[14] ?? 0);
            $grossSalary = floatval($row[31] ?? 0);
            $netSalary = floatval($row[48] ?? 0);

            // is_production logic
            $subDeptLower = strtolower($subDept);
            $isProduction = false;
            if (str_contains($subDeptLower, 'pabrikasi') ||
                str_contains($subDeptLower, 'elektrikal') ||
                str_contains($subDeptLower, 'logistik pabrik') ||
                str_contains($subDeptLower, 'produksi') ||
                str_contains($subDeptLower, 'maintenance')) {
                $isProduction = true;
            }

            $totalNetSalary += $netSalary;
            if (isset($entityCounts[$companyCode])) {
                $entityCounts[$companyCode]++;
            }

            $parsedData[] = [
                'company_code' => $companyCode,
                'period' => $period ?? $detectedPeriod,
                'group_name' => trim($row[3] ?? ''),
                'group_code' => trim($row[4] ?? ''),
                'sub_department' => $subDept,
                'job_title' => trim($row[8] ?? ''),
                'structural_status' => $structuralStatus,
                'nik' => $nik,
                'nama' => $nama,
                'basic_salary' => $basicSalary,
                'gross_salary' => $grossSalary,
                'net_salary' => $netSalary,
                'is_production' => $isProduction,
                'validation_warnings' => [],
            ];
        }

        if (empty($parsedData)) {
            throw new \Exception("Tidak ada data payroll yang valid dalam sheet 'TEMP'.");
        }

        $period = $detectedPeriod ?? date('Y-m');
        $reconciliation = $this->runReconciliation($parsedData, $period);

        return [
            'period' => $period,
            'total_rows' => count($parsedData),
            'entity_counts' => $entityCounts,
            'total_net_salary' => $totalNetSalary,
            'reconciliation' => $reconciliation,
            'data' => $parsedData,
        ];
    }

    /**
     * Run reconciliation and roster matching rules.
     */
    protected function runReconciliation(array &$parsedData, string $period): array
    {
        $year = substr($period, 0, 4);
        $monthNum = substr($period, 5, 2);
        $monthName = $this->months[$monthNum] ?? 'Maret';

        $templatesDir = base_path('docs/templates');
        
        // 1. Locate Roster and Rekap Files
        $rosterFile = null;
        $rekapFile = null;

        if (File::exists($templatesDir)) {
            $files = File::files($templatesDir);
            foreach ($files as $file) {
                $filename = $file->getFilename();
                // Check Roster file
                if (str_contains(strtolower($filename), 'list karyawan') && str_contains(strtolower($filename), strtolower($monthName))) {
                    $rosterFile = $file->getPathname();
                }
                // Check Rekap file
                if (str_contains(strtolower($filename), 'rekap pembayaran') && str_contains(strtolower($filename), strtolower($monthName))) {
                    $rekapFile = $file->getPathname();
                }
            }
        }

        $warnings = [];
        $rosterNiks = [];

        // 2. Load Active Roster NIKs
        if ($rosterFile && File::exists($rosterFile)) {
            try {
                $spreadsheet = IOFactory::load($rosterFile);
                // Load active roster sheets: STR1, STR2, NSTR
                $sheets = ['STR1', 'STR2', 'NSTR'];
                foreach ($sheets as $sheetName) {
                    $sheet = $spreadsheet->getSheetByName($sheetName);
                    if ($sheet) {
                        $rows = $sheet->toArray();
                        array_shift($rows); // Remove header
                        foreach ($rows as $row) {
                            // Column indices for NIK might differ, let's scan for NIK-like patterns or assume first non-empty column/column index 1
                            // In "3 LIST KARYAWAN Maret 2026.xlsx", let's check headers or look at Column B/C.
                            // Typically: Column A (No), Column B (NIK), Column C (Nama). Let's check both index 1 and 2
                            $nik1 = trim($row[1] ?? '');
                            $nik2 = trim($row[2] ?? '');
                            if ($nik1 !== '') {
                                $rosterNiks[$nik1] = true;
                            }
                            if ($nik2 !== '') {
                                $rosterNiks[$nik2] = true;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $warnings[] = "Gagal memuat file roster karyawan untuk validasi: " . $e->getMessage();
            }
        } else {
            $warnings[] = "File Roster Karyawan untuk bulan {$monthName} {$year} tidak ditemukan di {$templatesDir}.";
        }

        // 3. Roster Match Validation
        $unmatchedCount = 0;
        if (!empty($rosterNiks)) {
            foreach ($parsedData as &$row) {
                $nik = $row['nik'];
                if (!isset($rosterNiks[$nik])) {
                    $row['validation_warnings'][] = "NIK '{$nik}' tidak terdaftar di Roster Karyawan Aktif.";
                    $unmatchedCount++;
                }
            }
        }

        // 4. Load Rekap Aggregates & Compare
        $rekapData = [];
        $reconciliationWarning = null;

        if ($rekapFile && File::exists($rekapFile)) {
            try {
                $spreadsheet = IOFactory::load($rekapFile);
                
                // Let's compute sums by company code in our uploaded data
                $uploadedSums = [];
                foreach ($parsedData as $row) {
                    $cc = $row['company_code'];
                    $uploadedSums[$cc] = ($uploadedSums[$cc] ?? 0) + $row['net_salary'];
                }

                // Check sheets for NES and NPA
                foreach (['NES', 'NPA'] as $company) {
                    $sheet = $spreadsheet->getSheetByName($company);
                    if ($sheet) {
                        // We need to find the cell for "Gaji Diterima" or total for the current month
                        // In "3. Rekap Pembayaran Gaji Karyawan 2026 - Maret.xlsx", columns are typically structured:
                        // Row contains Department names, and columns represent months (Januari, Februari, Maret, dll.).
                        // Let's scan for the "Grand Total" or sum column for the month
                        $rows = $sheet->toArray();
                        $targetMonthColIndex = null;
                        
                        // Search for current month in header rows
                        foreach ($rows as $rowIndex => $rowCells) {
                            foreach ($rowCells as $colIndex => $val) {
                                if (is_string($val) && str_contains(strtolower($val), strtolower($monthName))) {
                                    $targetMonthColIndex = $colIndex;
                                    break 2;
                                }
                            }
                        }

                        if ($targetMonthColIndex !== null) {
                            // Find Grand Total row (usually has "Grand Total" or "TOTAL" in the first columns)
                            $rekapTotal = 0;
                            foreach ($rows as $rowCells) {
                                $firstVal = strtolower(trim($rowCells[0] ?? $rowCells[1] ?? ''));
                                if (str_contains($firstVal, 'total') || str_contains($firstVal, 'grand total') || str_contains($firstVal, 'jumlah')) {
                                    $rekapTotal = floatval($rowCells[$targetMonthColIndex] ?? 0);
                                    break;
                                }
                            }

                            if ($rekapTotal > 0) {
                                $uploadedTotal = $uploadedSums[$company] ?? 0;
                                $diff = abs($uploadedTotal - $rekapTotal);
                                
                                $rekapData[$company] = [
                                    'rekap_total' => $rekapTotal,
                                    'uploaded_total' => $uploadedTotal,
                                    'diff' => $diff,
                                    'status' => $diff < 1000 ? 'matched' : 'mismatched',
                                ];

                                if ($diff >= 1000) {
                                    $warnings[] = "Total Gaji Diterima untuk entitas {$company} selisih Rp " . number_format($diff, 0, ',', '.') . " dibanding Rekap Pembayaran Gaji.";
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $warnings[] = "Gagal memproses file rekap untuk rekonsiliasi: " . $e->getMessage();
            }
        } else {
            $warnings[] = "File Rekap Pembayaran Gaji untuk bulan {$monthName} {$year} tidak ditemukan di {$templatesDir}.";
        }

        return [
            'roster_checked' => !empty($rosterNiks),
            'unmatched_roster_count' => $unmatchedCount,
            'rekap_checked' => !empty($rekapData),
            'rekap_comparison' => $rekapData,
            'warnings' => $warnings,
        ];
    }
}
