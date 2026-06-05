<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ExcelParserService;
use App\Models\ImportHistory;
use App\Models\PayrollData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

use Illuminate\Support\Facades\Storage;

class PayrollImportController extends Controller
{
    protected ExcelParserService $parserService;

    public function __construct(ExcelParserService $parserService)
    {
        $this->parserService = $parserService;
    }

    /**
     * Show import form page.
     */
    public function showForm(): Response
    {
        return Inertia::render('Import/Upload');
    }

    /**
     * Process Excel file and show preview.
     */
    public function importPayroll(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $tempPath = $file->store('temp');

        try {
            $parsed = $this->parserService->parsePayroll(Storage::path($tempPath));

            // Clean up temp file
            Storage::delete($tempPath);

            return Inertia::render('Import/Preview', [
                'file_name' => $fileName,
                'period' => $parsed['period'],
                'total_rows' => $parsed['total_rows'],
                'entity_counts' => $parsed['entity_counts'],
                'total_net_salary' => $parsed['total_net_salary'],
                'reconciliation' => $parsed['reconciliation'],
                'data' => $parsed['data'],
            ]);
        } catch (\Exception $e) {
            Storage::delete($tempPath);
            return back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Commit previewed payroll to database.
     */
    public function savePayroll(Request $request)
    {
        $request->validate([
            'file_name' => ['required', 'string'],
            'period' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'data' => ['required', 'array'],
        ]);

        $period = $request->input('period');
        $fileName = $request->input('file_name');
        $records = $request->input('data');

        DB::beginTransaction();
        try {
            // Check if there is an existing import for this period/type.
            // If so, we'll overwrite it to maintain unique month data.
            $existing = ImportHistory::where('period', $period)
                ->where('type', 'payroll')
                ->first();
                
            if ($existing) {
                $existing->delete(); // Cascading delete will remove old payroll_data
            }

            $history = ImportHistory::create([
                'file_name' => $fileName,
                'period' => $period,
                'type' => 'payroll',
                'imported_by' => Auth::id(),
            ]);

            $batchData = [];
            foreach ($records as $row) {
                $batchData[] = [
                    'import_history_id' => $history->id,
                    'nik' => $row['nik'],
                    'nama' => $row['nama'],
                    'company_code' => $row['company_code'],
                    'group_name' => $row['group_name'] ?? null,
                    'group_code' => $row['group_code'] ?? null,
                    'sub_department' => $row['sub_department'] ?? null,
                    'job_title' => $row['job_title'] ?? null,
                    'structural_status' => $row['structural_status'],
                    'basic_salary' => floatval($row['basic_salary']),
                    'gross_salary' => floatval($row['gross_salary']),
                    'net_salary' => floatval($row['net_salary']),
                    'is_production' => filter_var($row['is_production'], FILTER_VALIDATE_BOOLEAN),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Chunk insert for sqlite limits
            foreach (array_chunk($batchData, 100) as $chunk) {
                PayrollData::insert($chunk);
            }

            DB::commit();
            return redirect()->route('dashboard')->with('success', "Data payroll periode {$period} berhasil disimpan.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('import.form')->with('error', 'Gagal menyimpan data ke database: ' . $e->getMessage());
        }
    }

    /**
     * Show import history list.
     */
    public function history(): Response
    {
        $histories = ImportHistory::with('importer')
            ->select('import_histories.*', DB::raw('(SELECT COUNT(*) FROM payroll_data WHERE import_history_id = import_histories.id) as row_count'))
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('History/Index', [
            'histories' => $histories,
        ]);
    }

    /**
     * Delete an import history record.
     */
    public function deleteHistory($id)
    {
        $history = ImportHistory::findOrFail($id);
        $period = $history->period;
        $history->delete();

        return back()->with('success', "Data payroll periode {$period} berhasil dihapus.");
    }

    /**
     * Download template files.
     */
    public function downloadTemplate(string $type)
    {
        $templatesDir = base_path('docs/templates');
        $fileName = null;

        if ($type === 'payroll') {
            $fileName = 'GAJI NES ALL DEPT MAR 2026 - Anas edited joined.xlsx';
        } elseif ($type === 'roster') {
            $fileName = '3 LIST KARYAWAN Maret 2026.xlsx';
        } elseif ($type === 'rekap') {
            $fileName = '3. Rekap Pembayaran Gaji Karyawan 2026 - Maret.xlsx';
        }

        if ($fileName && file_exists($templatesDir . '/' . $fileName)) {
            return response()->download($templatesDir . '/' . $fileName);
        }

        abort(404, 'Template tidak ditemukan.');
    }
}
