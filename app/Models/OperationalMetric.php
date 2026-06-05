<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationalMetric extends Model
{
    protected $fillable = [
        'period',
        'total_sales',
        'production_volume',
        'production_cost',
    ];

    protected $casts = [
        'total_sales' => 'decimal:2',
        'production_volume' => 'decimal:2',
        'production_cost' => 'decimal:2',
    ];
}
