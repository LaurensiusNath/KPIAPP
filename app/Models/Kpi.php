<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kpi extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period_id',
        'title',
        'weight',
        'criteria_scale',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'criteria_scale' => 'array', // JSON <-> array
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class, 'period_id', 'id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(KpiValue::class, 'kpi_id', 'id');
    }

    public function kpiValues(): HasMany
    {
        return $this->hasMany(KpiValue::class, 'kpi_id', 'id');
    }
}
