<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Period;
use App\Services\Exceptions\DomainValidationException;
use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\DB;

class PeriodService
{
    public function __construct(
        private readonly DatabaseManager $db
    ) {}

    public function createPeriod(int $year, int $semester): Period
    {
        // Prevent duplicates (unique by year, semester)
        $exists = Period::query()
            ->where('year', $year)
            ->where('semester', $semester)
            ->exists();

        if ($exists) {
            throw new DomainValidationException('Periode sudah ada.');
        }

        return Period::create([
            'year' => $year,
            'semester' => $semester,
            'is_active' => false,
        ]);
    }

    public function setActivePeriod(Period $period): Period
    {
        $this->db->transaction(function () use ($period) {
            // Deactivate all
            Period::query()->update(['is_active' => false]);
            // Activate selected
            $period->update(['is_active' => true]);
        });

        return $period->refresh();
    }

    public function findById(int $periodId): ?Period
    {
        return Period::query()->find($periodId);
    }

    public function setActivePeriodById(int $periodId): Period
    {
        $period = Period::query()->findOrFail($periodId);
        return $this->setActivePeriod($period);
    }

    public function getActivePeriod(): ?Period
    {
        return Period::query()->where('is_active', true)->first();
    }

    public function loadKpiCount(Period $period): Period
    {
        return $period->loadCount('kpis');
    }

    public function isCurrentWindowForKpiCreation(Period $period): bool
    {
        $now = Carbon::now();

        if ($period->semester === 1) {
            return (int)$now->month === 1 && (int)$now->day <= 10;
        }

        if ($period->semester === 2) {
            return (int)$now->month === 7 && (int)$now->day <= 10;
        }

        return false;
    }

    public function isCurrentWindowForAppraisal(Period $period): bool
    {
        $now = Carbon::now();

        if ((int)$period->year !== (int)$now->year) {
            return false;
        }

        if ($period->semester === 1) {
            return (int)$now->month === 6 && $now->day >= 25 && $now->day <= 31;
        }

        if ($period->semester === 2) {
            return (int)$now->month === 12 && $now->day >= 25 && $now->day <= 31;
        }

        return false;
    }
}
