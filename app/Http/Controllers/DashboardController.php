<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ManpowerAnalysisService;
use App\Models\PayrollData;
use App\Models\ImportHistory;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DashboardController extends Controller
{
    protected ManpowerAnalysisService $analysisService;

    public function __construct(ManpowerAnalysisService $analysisService)
    {
        $this->analysisService = $analysisService;
    }

    /**
     * Show dashboard index.
     */
    public function index(Request $request): Response
    {
        $trends = $this->analysisService->getMonthlyTrends();

        // Determine selected period
        $availablePeriods = collect($trends)->pluck('period')->toArray();
        $selectedPeriod = $request->input('period');
        
        if (!$selectedPeriod && !empty($availablePeriods)) {
            $selectedPeriod = end($availablePeriods); // default to latest month
        }

        // 1. Fetch Department Details for Selected Period
        $departments = [];
        if ($selectedPeriod) {
            $departments = DB::table('payroll_data')
                ->join('import_histories', 'payroll_data.import_history_id', '=', 'import_histories.id')
                ->where('import_histories.period', $selectedPeriod)
                ->select(
                    'payroll_data.sub_department as department',
                    'payroll_data.is_production',
                    DB::raw('COUNT(DISTINCT payroll_data.nik) as staff_count'),
                    DB::raw('SUM(payroll_data.basic_salary) as total_basic_salary'),
                    DB::raw('SUM(payroll_data.net_salary) as total_net_salary')
                )
                ->groupBy('payroll_data.sub_department', 'payroll_data.is_production')
                ->orderBy('total_net_salary', 'desc')
                ->get();
        }

        // 2. Generate AI Insights dynamically
        $aiInsight = $this->generateAiInsights($trends, $selectedPeriod);

        // 3. Find active period info
        $currentTrend = collect($trends)->firstWhere('period', $selectedPeriod);

        return Inertia::render('Dashboard/Index', [
            'trends' => $trends,
            'availablePeriods' => $availablePeriods,
            'selectedPeriod' => $selectedPeriod,
            'departments' => $departments,
            'currentTrend' => $currentTrend,
            'aiInsight' => $aiInsight,
        ]);
    }

    /**
     * Generate analytical AI insight paragraph.
     */
    protected function generateAiInsights(array $trends, ?string $selectedPeriod): array
    {
        if (empty($trends) || !$selectedPeriod) {
            return [
                'title' => 'Belum Ada Data',
                'summary' => 'Unggah file Excel payroll dan input metrik operasional untuk mengaktifkan AI Executive Assistant.',
                'recommendation' => 'Silakan navigasi ke menu "Import Data" untuk memulai.',
            ];
        }

        $current = collect($trends)->firstWhere('period', $selectedPeriod);
        if (!$current) {
            $current = end($trends);
        }

        $periodFormatted = $current['period_formatted'] ?? $selectedPeriod;
        
        // Find previous month to check trend direction
        $currentIndex = collect($trends)->search(fn($item) => $item['period'] === $current['period']);
        $prev = ($currentIndex !== false && $currentIndex > 0) ? $trends[$currentIndex - 1] : null;

        $salaryChangeText = "beban manpower stabil";
        if ($prev) {
            $diffSal = $current['total_manpower_cost'] - $prev['total_manpower_cost'];
            $pctSal = round(($diffSal / ($prev['total_manpower_cost'] ?: 1)) * 100, 2);
            if ($pctSal > 0) {
                $salaryChangeText = "beban manpower naik sebesar <strong>{$pctSal}%</strong> dibanding bulan lalu";
            } elseif ($pctSal < 0) {
                $absPct = abs($pctSal);
                $salaryChangeText = "beban manpower turun sebesar <strong>{$absPct}%</strong> dibanding bulan lalu";
            }
        }

        $ratio = $current['labor_to_sales_ratio'];
        $ratioText = "Rasio manpower vs penjualan berada di level <strong>{$ratio}%</strong>";
        if ($ratio > 0) {
            if ($ratio <= 10) {
                $ratioText .= ", tingkat efisiensi <strong>sangat tinggi</strong> (optimal di bawah 15%)";
            } elseif ($ratio <= 15) {
                $ratioText .= ", tingkat efisiensi <strong>baik</strong> (dalam batas aman 15%)";
            } else {
                $ratioText .= ", tingkat efisiensi <strong>kritis</strong> (melebihi batas aman 15%)";
            }
        }

        $prodCostRatio = $current['labor_to_prod_cost_ratio'];
        $prodText = "";
        if ($prodCostRatio > 0) {
            $prodText = " Biaya gaji tenaga kerja produksi menyumbang sekitar <strong>{$prodCostRatio}%</strong> dari total biaya produksi.";
        }

        $recommendation = "Data operasional optimal. Pertahankan rasio pengeluaran SDM agar tidak melebihi 15% dari penjualan bulanan.";
        if ($ratio > 15) {
            $recommendation = "Rasio SDM terhadap Penjualan melebihi batas aman 15%. Disarankan untuk mengendalikan overtime (lembur) karyawan non-produksi atau melakukan efisiensi kapasitas operasional.";
        } elseif ($current['labor_cost_per_unit'] > 0 && $prev && $current['labor_cost_per_unit'] > $prev['labor_cost_per_unit']) {
            $recommendation = "Biaya tenaga kerja produksi per unit meningkat. Disarankan memantau efisiensi shift kerja pabrikasi guna menekan biaya per unit volume.";
        }

        return [
            'title' => "Analisis Kinerja {$periodFormatted}",
            'summary' => "Pada periode {$periodFormatted}, {$salaryChangeText}. {$ratioText}.{$prodText}",
            'recommendation' => $recommendation,
        ];
    }

    /**
     * Show detailed payroll list.
     */
    public function detail(Request $request): Response
    {
        $trends = $this->analysisService->getMonthlyTrends();
        $availablePeriods = collect($trends)->pluck('period')->toArray();
        $selectedPeriod = $request->input('period');
        
        if (!$selectedPeriod && !empty($availablePeriods)) {
            $selectedPeriod = end($availablePeriods);
        }

        $search = $request->input('search');
        $company = $request->input('company');
        $structural = $request->input('structural');
        $function = $request->input('function');

        $query = DB::table('payroll_data')
            ->join('import_histories', 'payroll_data.import_history_id', '=', 'import_histories.id')
            ->where('import_histories.period', $selectedPeriod)
            ->select('payroll_data.*');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('payroll_data.nama', 'like', '%' . $search . '%')
                  ->orWhere('payroll_data.nik', 'like', '%' . $search . '%');
            });
        }

        if ($company) {
            $query->where('payroll_data.company_code', $company);
        }

        if ($structural) {
            $query->where('payroll_data.structural_status', $structural);
        }

        if ($function !== null && $function !== '') {
            $query->where('payroll_data.is_production', filter_var($function, FILTER_VALIDATE_BOOLEAN));
        }

        $records = $query->orderBy('nama', 'asc')->get();

        return Inertia::render('Dashboard/Detail', [
            'availablePeriods' => $availablePeriods,
            'selectedPeriod' => $selectedPeriod,
            'records' => $records,
            'filters' => [
                'search' => $search,
                'company' => $company,
                'structural' => $structural,
                'function' => $function,
            ],
        ]);
    }

    /**
     * Export detailed payroll to Excel.
     */
    public function exportPayrollExcel(Request $request)
    {
        $trends = $this->analysisService->getMonthlyTrends();
        $availablePeriods = collect($trends)->pluck('period')->toArray();
        $selectedPeriod = $request->input('period');
        
        if (!$selectedPeriod && !empty($availablePeriods)) {
            $selectedPeriod = end($availablePeriods);
        }

        $search = $request->input('search');
        $company = $request->input('company');
        $structural = $request->input('structural');
        $function = $request->input('function');

        $query = DB::table('payroll_data')
            ->join('import_histories', 'payroll_data.import_history_id', '=', 'import_histories.id')
            ->where('import_histories.period', $selectedPeriod)
            ->select('payroll_data.*');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('payroll_data.nama', 'like', '%' . $search . '%')
                  ->orWhere('payroll_data.nik', 'like', '%' . $search . '%');
            });
        }

        if ($company) {
            $query->where('payroll_data.company_code', $company);
        }

        if ($structural) {
            $query->where('payroll_data.structural_status', $structural);
        }

        if ($function !== null && $function !== '') {
            $query->where('payroll_data.is_production', filter_var($function, FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
        }

        $records = $query->orderBy('nama', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Payroll Detail');

        // Set headers
        $headers = [
            'NIK', 'Nama Karyawan', 'Entitas', 'Sub Departemen', 'Jabatan', 
            'Struktural', 'Fungsi Kerja', 'Gaji Pokok', 'Gaji Bruto', 'Gaji Diterima (Nett)'
        ];
        
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
            $sheet->getStyle($colLetter . '1')->getFont()->setBold(true);
        }

        // Fill data
        $rowNumber = 2;
        foreach ($records as $record) {
            $sheet->setCellValue('A' . $rowNumber, $record->nik);
            $sheet->setCellValue('B' . $rowNumber, $record->nama);
            $sheet->setCellValue('C' . $rowNumber, $record->company_code);
            $sheet->setCellValue('D' . $rowNumber, $record->sub_department ?? '-');
            $sheet->setCellValue('E' . $rowNumber, $record->job_title ?? '-');
            $sheet->setCellValue('F' . $rowNumber, $record->structural_status);
            $sheet->setCellValue('G' . $rowNumber, $record->is_production ? 'Production' : 'Non-Production');
            
            $sheet->setCellValue('H' . $rowNumber, $record->basic_salary);
            $sheet->setCellValue('I' . $rowNumber, $record->gross_salary);
            $sheet->setCellValue('J' . $rowNumber, $record->net_salary);

            // Format numeric salaries
            $sheet->getStyle('H' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('I' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('J' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');

            $rowNumber++;
        }

        // Auto size columns
        foreach (range(1, 10) as $col) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $fileName = "Payroll_Detail_{$selectedPeriod}.xlsx";
        
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export comparison trends to Excel.
     */
    public function exportTrendsExcel(Request $request)
    {
        $trends = $this->analysisService->getMonthlyTrends();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Monthly Trends');

        // Set headers
        $headers = [
            'Periode', 'Jumlah Karyawan', 'Karyawan Prod', 'Karyawan Non-Prod',
            'Total Beban Manpower', 'Beban Manpower Prod', 'Beban Manpower Non-Prod',
            'Biaya STR1', 'Biaya STR2', 'Biaya NSTR',
            'Total Penjualan', 'Volume Produksi', 'Biaya Produksi',
            'Rasio Manpower vs Penjualan (%)', 'Biaya Manpower per Unit', 'Rasio Manpower vs Biaya Prod (%)'
        ];

        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
            $sheet->getStyle($colLetter . '1')->getFont()->setBold(true);
        }

        // Fill data
        $rowNumber = 2;
        foreach ($trends as $trend) {
            $sheet->setCellValue('A' . $rowNumber, $trend['period_formatted']);
            $sheet->setCellValue('B' . $rowNumber, $trend['employee_count']);
            $sheet->setCellValue('C' . $rowNumber, $trend['prod_employee_count']);
            $sheet->setCellValue('D' . $rowNumber, $trend['non_prod_employee_count']);
            
            $sheet->setCellValue('E' . $rowNumber, $trend['total_manpower_cost']);
            $sheet->setCellValue('F' . $rowNumber, $trend['prod_manpower_cost']);
            $sheet->setCellValue('G' . $rowNumber, $trend['non_prod_manpower_cost']);
            $sheet->setCellValue('H' . $rowNumber, $trend['str1_cost']);
            $sheet->setCellValue('I' . $rowNumber, $trend['str2_cost']);
            $sheet->setCellValue('J' . $rowNumber, $trend['nstr_cost']);
            
            $sheet->setCellValue('K' . $rowNumber, $trend['total_sales']);
            $sheet->setCellValue('L' . $rowNumber, $trend['production_volume']);
            $sheet->setCellValue('M' . $rowNumber, $trend['production_cost']);
            
            $sheet->setCellValue('N' . $rowNumber, $trend['labor_to_sales_ratio']);
            $sheet->setCellValue('O' . $rowNumber, $trend['labor_cost_per_unit']);
            $sheet->setCellValue('P' . $rowNumber, $trend['labor_to_prod_cost_ratio']);

            // Format numbers
            $sheet->getStyle('B' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('C' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('D' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
            
            $sheet->getStyle('E' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('F' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('G' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('H' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('I' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('J' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
            
            $sheet->getStyle('K' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('L' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('M' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
            
            $sheet->getStyle('N' . $rowNumber)->getNumberFormat()->setFormatCode('0.00"%"');
            $sheet->getStyle('O' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('P' . $rowNumber)->getNumberFormat()->setFormatCode('0.00"%"');

            $rowNumber++;
        }

        // Auto size columns
        foreach (range(1, 16) as $col) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $fileName = "Monthly_Trends_Summary.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
