<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OperationalMetric;

class OperationalMetricController extends Controller
{
    /**
     * Store or update operational metrics.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'period' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'total_sales' => ['required', 'numeric', 'min:0'],
            'production_volume' => ['required', 'numeric', 'min:0'],
            'production_cost' => ['required', 'numeric', 'min:0'],
        ]);

        OperationalMetric::updateOrCreate(
            ['period' => $validated['period']],
            [
                'total_sales' => $validated['total_sales'],
                'production_volume' => $validated['production_volume'],
                'production_cost' => $validated['production_cost'],
            ]
        );

        return back()->with('success', "Metrik operasional periode {$validated['period']} berhasil disimpan.");
    }
}
