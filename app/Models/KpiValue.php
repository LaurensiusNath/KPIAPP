<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'kpi_id',
        'user_id',
        'evaluator_id',
        'period_id',
        'division_id',
        'month',
        'score',
        'note',
        'is_submitted',
    ];

    protected $casts = [
        'score' => 'integer',
        'is_submitted' => 'boolean',
    ];

    public function kpi(): BelongsTo
    {
        return $this->belongsTo(Kpi::class, 'kpi_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id', 'id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class, 'period_id', 'id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id', 'id');
    }
}
