<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportHistory extends Model
{
    protected $fillable = [
        'file_name',
        'period',
        'type',
        'imported_by',
    ];

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(PayrollData::class, 'import_history_id');
    }
}
