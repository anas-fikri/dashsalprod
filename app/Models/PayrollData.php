<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollData extends Model
{
    protected $table = 'payroll_data';

    protected $fillable = [
        'import_history_id',
        'nik',
        'nama',
        'company_code',
        'group_name',
        'group_code',
        'sub_department',
        'job_title',
        'structural_status',
        'basic_salary',
        'gross_salary',
        'net_salary',
        'is_production',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'is_production' => 'boolean',
    ];

    public function importHistory(): BelongsTo
    {
        return $this->belongsTo(ImportHistory::class, 'import_history_id');
    }
}
