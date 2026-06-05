<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ImportHistory;
use App\Models\PayrollData;
use App\Models\OperationalMetric;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleData2000Seeder extends Seeder
{
    /**
     * Run the database seeds for Year 2000 sample data.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $admin = User::first();
        }

        $adminId = $admin ? $admin->id : 1;

        // Sample employees database definition to generate realistic records
        $employees = [
            // NES
            ['nik' => '200001001', 'nama' => 'Aditya Wijaya', 'company_code' => 'NES', 'sub_dept' => 'Pabrikasi', 'job' => 'Operator Welder', 'status' => 'NSTR', 'basic' => 4500000, 'net' => 5200000, 'is_prod' => true],
            ['nik' => '200001002', 'nama' => 'Budi Santoso', 'company_code' => 'NES', 'sub_dept' => 'Pabrikasi', 'job' => 'Kepala Regu Pabrikasi', 'status' => 'STR2', 'basic' => 6000000, 'net' => 7100000, 'is_prod' => true],
            ['nik' => '200001003', 'nama' => 'Candra Kirana', 'company_code' => 'NES', 'sub_dept' => 'Logistik Pabrik', 'job' => 'Staff Logistik', 'status' => 'NSTR', 'basic' => 4200000, 'net' => 4800000, 'is_prod' => true],
            ['nik' => '200001004', 'nama' => 'Dedi Setiadi', 'company_code' => 'NES', 'sub_dept' => 'Maintenance', 'job' => 'Teknisi Pabrik', 'status' => 'NSTR', 'basic' => 4400000, 'net' => 5100000, 'is_prod' => true],
            ['nik' => '200001005', 'nama' => 'Elisa Rahma', 'company_code' => 'NES', 'sub_dept' => 'Keuangan', 'job' => 'Staff Accounting', 'status' => 'NSTR', 'basic' => 4600000, 'net' => 5000000, 'is_prod' => false],
            ['nik' => '200001006', 'nama' => 'Farhan Majid', 'company_code' => 'NES', 'sub_dept' => 'HRD', 'job' => 'Manager HRD', 'status' => 'STR1', 'basic' => 12000000, 'net' => 14500000, 'is_prod' => false],

            // NPA
            ['nik' => '200002001', 'nama' => 'Gita Permata', 'company_code' => 'NPA', 'sub_dept' => 'Marketing', 'job' => 'Staff Marketing', 'status' => 'NSTR', 'basic' => 4500000, 'net' => 5600000, 'is_prod' => false],
            ['nik' => '200002002', 'nama' => 'Hendra Wijaya', 'company_code' => 'NPA', 'sub_dept' => 'Marketing', 'job' => 'Kadept Marketing', 'status' => 'STR1', 'basic' => 11000000, 'net' => 13500000, 'is_prod' => false],
            ['nik' => '200002003', 'nama' => 'Indra Lesmana', 'company_code' => 'NPA', 'sub_dept' => 'Produksi', 'job' => 'Operator CNC', 'status' => 'NSTR', 'basic' => 4700000, 'net' => 5500000, 'is_prod' => true],
            ['nik' => '200002004', 'nama' => 'Joko Wahyudi', 'company_code' => 'NPA', 'sub_dept' => 'Produksi', 'job' => 'Kabag Produksi', 'status' => 'STR2', 'basic' => 6500000, 'net' => 7800000, 'is_prod' => true],
            ['nik' => '200002005', 'nama' => 'Kartika Sari', 'company_code' => 'NPA', 'sub_dept' => 'IT', 'job' => 'Staff IT Support', 'status' => 'NSTR', 'basic' => 4600000, 'net' => 5100000, 'is_prod' => false],
        ];

        $periods = [
            '2000-01' => [
                'sales' => 11000000000.00,
                'prod_cost' => 7500000000.00,
                'volume' => 32000.00,
                'salary_factor' => 0.98, // slightly lower salaries in jan
            ],
            '2000-02' => [
                'sales' => 11500000000.00,
                'prod_cost' => 7800000000.00,
                'volume' => 34000.00,
                'salary_factor' => 1.00,
            ],
            '2000-03' => [
                'sales' => 12400000000.00,
                'prod_cost' => 8200000000.00,
                'volume' => 38000.00,
                'salary_factor' => 1.02, // slightly higher due to overtime/production increase
            ]
        ];

        DB::beginTransaction();
        try {
            foreach ($periods as $period => $meta) {
                // 1. Seed Operational Metrics
                OperationalMetric::updateOrCreate(
                    ['period' => $period],
                    [
                        'total_sales' => $meta['sales'],
                        'production_cost' => $meta['prod_cost'],
                        'production_volume' => $meta['volume'],
                    ]
                );

                // 2. Seed Import History for Payroll
                $history = ImportHistory::updateOrCreate(
                    ['period' => $period, 'type' => 'payroll'],
                    [
                        'file_name' => "GAJI_NES_ALL_DEPT_SAMPLE_{$period}.xlsx",
                        'imported_by' => $adminId,
                    ]
                );

                // Clear existing payroll_data for this history to prevent duplicate key errors
                PayrollData::where('import_history_id', $history->id)->delete();

                // 3. Seed Payroll Data rows
                $payrollRows = [];
                foreach ($employees as $emp) {
                    $basicFactor = $meta['salary_factor'];
                    $grossFactor = $meta['salary_factor'] * 1.15;
                    
                    $basic = $emp['basic'] * $basicFactor;
                    $net = $emp['net'] * $basicFactor;
                    $gross = $basic * $grossFactor;

                    $payrollRows[] = [
                        'import_history_id' => $history->id,
                        'nik' => $emp['nik'],
                        'nama' => $emp['nama'],
                        'company_code' => $emp['company_code'],
                        'group_name' => $emp['status'] === 'NSTR' ? '0300 - Non Struktural' : ($emp['status'] === 'STR2' ? '0200 - Struktural 2' : '0100 - Struktural 1'),
                        'group_code' => $emp['status'] === 'NSTR' ? '0300' : ($emp['status'] === 'STR2' ? '0200' : '0100'),
                        'sub_department' => $emp['sub_dept'],
                        'job_title' => $emp['job'],
                        'structural_status' => $emp['status'],
                        'basic_salary' => $basic,
                        'gross_salary' => $gross,
                        'net_salary' => $net,
                        'is_production' => $emp['is_prod'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                PayrollData::insert($payrollRows);
            }

            DB::commit();
            $this->command->info("Data sample tahun 2000 (Jan - Mar) berhasil di-seed!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Gagal melakukan seeder data 2000: " . $e->getMessage());
        }
    }
}
