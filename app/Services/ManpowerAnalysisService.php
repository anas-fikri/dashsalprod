<?php

namespace App\Services;

use App\Models\PayrollData;
use App\Models\OperationalMetric;
use App\Models\ImportHistory;
use Illuminate\Support\Facades\DB;

class ManpowerAnalysisService
{
    /**
     * Get aggregated monthly trends for comparison dashboard.
     */
    public function getMonthlyTrends(): array
    {
        // Get all periods from payroll data and operational metrics
        $payrollPeriods = DB::table('payroll_data')
            ->join('import_histories', 'payroll_data.import_history_id', '=', 'import_histories.id')
            ->select('import_histories.period')
            ->distinct()
            ->pluck('period')
            ->toArray();

        $metricPeriods = OperationalMetric::select('period')
            ->distinct()
            ->pluck('period')
            ->toArray();

        $allPeriods = array_unique(array_merge($payrollPeriods, $metricPeriods));
        sort($allPeriods);

        $trends = [];

        foreach ($allPeriods as $period) {
            // 1. Calculate Payroll Aggregations
            $payrollAgg = DB::table('payroll_data')
                ->join('import_histories', 'payroll_data.import_history_id', '=', 'import_histories.id')
                ->where('import_histories.period', $period)
                ->select(
                    DB::raw('SUM(net_salary) as total_net_salary'),
                    DB::raw('SUM(gross_salary) as total_gross_salary'),
                    DB::raw('SUM(CASE WHEN is_production = 1 THEN net_salary ELSE 0 END) as prod_net_salary'),
                    DB::raw('SUM(CASE WHEN is_production = 0 THEN net_salary ELSE 0 END) as non_prod_net_salary'),
                    DB::raw('SUM(CASE WHEN structural_status = "STR1" THEN net_salary ELSE 0 END) as str1_salary'),
                    DB::raw('SUM(CASE WHEN structural_status = "STR2" THEN net_salary ELSE 0 END) as str2_salary'),
                    DB::raw('SUM(CASE WHEN structural_status = "NSTR" THEN net_salary ELSE 0 END) as nstr_salary'),
                    DB::raw('COUNT(DISTINCT nik) as employee_count'),
                    DB::raw('COUNT(DISTINCT CASE WHEN is_production = 1 THEN nik END) as prod_employee_count'),
                    DB::raw('COUNT(DISTINCT CASE WHEN is_production = 0 THEN nik END) as non_prod_employee_count')
                )
                ->first();

            // 2. Fetch Operational Metrics
            $metrics = OperationalMetric::where('period', $period)->first();

            $totalSales = $metrics ? floatval($metrics->total_sales) : 0;
            $productionVolume = $metrics ? floatval($metrics->production_volume) : 0;
            $productionCost = $metrics ? floatval($metrics->production_cost) : 0;

            $totalManpowerCost = $payrollAgg ? floatval($payrollAgg->total_net_salary) : 0;
            $prodManpowerCost = $payrollAgg ? floatval($payrollAgg->prod_net_salary) : 0;
            $nonProdManpowerCost = $payrollAgg ? floatval($payrollAgg->non_prod_net_salary) : 0;

            // 3. Compute KPI ratios
            $laborToSalesRatio = $totalSales > 0 ? ($totalManpowerCost / $totalSales) * 100 : 0;
            $laborCostPerUnit = $productionVolume > 0 ? ($prodManpowerCost / $productionVolume) : 0;
            $laborToProdCostRatio = $productionCost > 0 ? ($prodManpowerCost / $productionCost) * 100 : 0;

            $trends[] = [
                'period' => $period,
                'period_formatted' => $this->formatPeriod($period),
                'employee_count' => $payrollAgg ? intval($payrollAgg->employee_count) : 0,
                'prod_employee_count' => $payrollAgg ? intval($payrollAgg->prod_employee_count) : 0,
                'non_prod_employee_count' => $payrollAgg ? intval($payrollAgg->non_prod_employee_count) : 0,
                
                // Salaries
                'total_manpower_cost' => $totalManpowerCost,
                'prod_manpower_cost' => $prodManpowerCost,
                'non_prod_manpower_cost' => $nonProdManpowerCost,
                'str1_cost' => $payrollAgg ? floatval($payrollAgg->str1_salary) : 0,
                'str2_cost' => $payrollAgg ? floatval($payrollAgg->str2_salary) : 0,
                'nstr_cost' => $payrollAgg ? floatval($payrollAgg->nstr_salary) : 0,

                // Operational metrics
                'total_sales' => $totalSales,
                'production_volume' => $productionVolume,
                'production_cost' => $productionCost,

                // Computed KPIs
                'labor_to_sales_ratio' => round($laborToSalesRatio, 2),
                'labor_cost_per_unit' => round($laborCostPerUnit, 2),
                'labor_to_prod_cost_ratio' => round($laborToProdCostRatio, 2),
            ];
        }

        return $trends;
    }

    /**
     * Format period (YYYY-MM) to Indonesian (e.g. Maret 2026).
     */
    protected function formatPeriod(string $period): string
    {
        $year = substr($period, 0, 4);
        $month = substr($period, 5, 2);
        
        $months = [
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

        return ($months[$month] ?? $month) . ' ' . $year;
    }
}
