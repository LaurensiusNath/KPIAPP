<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appraisal extends Model
{
    /** @use HasFactory<\Database\Factories\AppraisalFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'evaluator_id',
        'division_id',
        'period_id',
        'final_score',
        'comment_teamleader',
        'comment_hrd',
        'is_finalized',
        'teamleader_submitted_at',
        'hrd_submitted_at',

    ];

    protected $casts = [
        'final_score' => 'decimal:2',
        'is_finalized' => 'boolean',
        'teamleader_submitted_at' => 'datetime',
        'hrd_submitted_at' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function period()
    {
        return $this->belongsTo(Period::class);
    }
}
