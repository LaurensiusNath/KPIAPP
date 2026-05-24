<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appraisal extends Model
{
    /** @use HasFactory<\Database\Factories\AppraisalFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'team_leader_id',
        'division_id',
        'period_id',
        'final_score',
        'comment_teamleader',
        'comment_hrd',
        'status',
        'teamleader_submitted_at',
        'hrd_submitted_at',

    ];

    protected $casts = [
        'final_score' => 'decimal:2',
        'status' => 'string',
        'teamleader_submitted_at' => 'datetime',
        'hrd_submitted_at' => 'datetime',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_leader_id', 'id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id', 'id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class, 'period_id', 'id');
    }

    public function isPendingTeamLeader(): bool
    {
        return $this->status === 'pending_teamleader';
    }

    public function isPendingHrd(): bool
    {
        return $this->status === 'pending_hrd';
    }

    public function isFinalized(): bool
    {
        return $this->status === 'finalized';
    }

    // State transition methods
    public function markTeamLeaderSubmitted(): void
    {
        $this->status = 'pending_hrd';
        $this->teamleader_submitted_at = now();
        $this->save();
    }

    public function markHrdSubmitted(): void
    {
        $this->status = 'finalized';
        $this->hrd_submitted_at = now();
        $this->save();
    }
}
