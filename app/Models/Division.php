<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Appraisal;

class Division extends Model
{
    /** @use HasFactory<\Database\Factories\DivisionFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'leader_id',
    ];

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id', 'id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'division_id', 'id')
            ->where('id', '!=', $this->leader_id);
    }

    public function appraisals(): HasMany
    {
        return $this->hasMany(Appraisal::class, 'division_id', 'id');
    }
}
