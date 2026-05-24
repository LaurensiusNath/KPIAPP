<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Period extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'semester',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function kpis(): HasMany
    {
        return $this->hasMany(Kpi::class, 'period_id', 'id');
    }

    public function kpiValues(): HasMany
    {
        return $this->hasMany(KpiValue::class, 'period_id', 'id');
    }

    public function appraisals(): HasMany
    {
        return $this->hasMany(Appraisal::class, 'period_id', 'id');
    }
}
