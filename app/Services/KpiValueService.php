<?php

namespace App\Services;

use App\Models\Division;
use App\Models\Kpi;
use App\Models\KpiValue;
use App\Models\Period;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\Exceptions\DomainValidationException;

class KpiValueService
{
    // --------------- Validation helpers ---------------
    public function isEvaluationWindow(?Carbon $now = null): bool
    {
        $now = $now ?: now();
        $day = (int)$now->day;
        return $day >= 21 && $day <= 25;
    }

    public function ensureTeamLeader(User $tl): void
    {
        if (($tl->role ?? null) !== 'team-leader') {
            abort(403, 'Hanya Team Leader yang diperbolehkan menilai.');
        }
    }

    public function ensureSameDivision(User $tl, User $user): void
    {
        if (($tl->division_id ?? null) === null || $tl->division_id !== $user->division_id) {
            abort(403, 'Team Leader hanya boleh menilai anggota di divisinya.');
        }
    }

    public function ensureActivePeriod(?Period $period): void
    {
        if (!$period || !$period->is_active) {
            abort(403, 'Periode aktif tidak ditemukan.');
        }
    }

    public function ensurePeriodMatchesCurrentDate(Period $period, ?Carbon $now = null): void
    {
        $now = $now ?: now();
        if ((int)$period->year !== (int)$now->year) {
            throw new DomainValidationException('Tahun periode aktif tidak sesuai tahun berjalan.');
        }

        $month = (int)$now->month;

        // Semester 1 seharusnya bulan 1–6, Semester 2 seharusnya 7–12
        if ($period->semester === 1 && $month > 6) {
            throw new DomainValidationException('Periode semester 1 tidak berlaku untuk bulan ' . $now->format('F') . '.');
        }

        if ($period->semester === 2 && $month < 7) {
            throw new DomainValidationException('Periode semester 2 belum dimulai untuk bulan ' . $now->format('F') . '.');
        }
    }

    public function alreadySubmitted(User $user, Period $period, int $month): bool
    {
        return KpiValue::where('user_id', $user->id)
            ->where('period_id', $period->id)
            ->where('month', $month)
            ->where('is_submitted', true)
            ->exists();
    }

    // --------------- Retrieval ---------------
    public function getMembersForTeamLeader(User $tl)
    {
        $this->ensureTeamLeader($tl);
        return User::query()
            ->where('division_id', $tl->division_id)
            ->where('id', '!=', $tl->id)
            ->whereIn('role', ['user'])
            ->orderBy('name')
            ->get();
    }

    public function getUserKpisForPeriod(User $user, Period $period)
    {
        $this->ensureActivePeriod($period);
        return Kpi::query()
            ->where('user_id', $user->id)
            ->where('period_id', $period->id)
            ->orderBy('created_at')
            ->get();
    }

    public function getMonthlyValues(User $user, Period $period, int $month)
    {
        return KpiValue::query()
            ->where('user_id', $user->id)
            ->where('period_id', $period->id)
            ->where('month', $month)
            ->get()
            ->keyBy('kpi_id');
    }

    public function getOrCreateValue(Kpi $kpi, User $user, User $evaluator, Period $period, int $month): KpiValue
    {
        return KpiValue::firstOrCreate([
            'kpi_id' => $kpi->id,
            'user_id' => $user->id,
            'period_id' => $period->id,
            'month' => $month,
        ], [
            'evaluator_id' => $evaluator->id,
            'division_id' => $user->division_id,
            // placeholder score agar lolos NOT NULL; nanti di-update dengan nilai sebenarnya
            'score' => 0,
            'is_submitted' => false,
        ]);
    }

    // --------------- Submit ---------------
    public function submitMonthlyEvaluation(User $user, User $tl, array $scores, array $notes): array
    {
        $this->ensureTeamLeader($tl);
        $this->ensureSameDivision($tl, $user);

        /** @var PeriodService $periodService */
        $periodService = app(PeriodService::class);
        $period = $periodService->getActivePeriod();
        $this->ensureActivePeriod($period);
        try {
            $this->ensurePeriodMatchesCurrentDate($period);
        } catch (DomainValidationException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        $month = (int) now()->month;
        if (!$this->isEvaluationWindow()) {
            return [
                'success' => false,
                'message' => 'Penilaian hanya diperbolehkan tanggal 21–25 setiap bulan.',
            ];
        }

        if ($this->alreadySubmitted($user, $period, $month)) {
            return [
                'success' => false,
                'message' => 'Penilaian bulan ini sudah disubmit.',
            ];
        }

        $kpis = $this->getUserKpisForPeriod($user, $period);
        if ($kpis->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada item KPI untuk user ini pada periode aktif.',
            ];
        }

        DB::beginTransaction();
        try {
            // Pastikan semua KPI memiliki skor terkirim
            foreach ($kpis as $kpi) {
                if (!array_key_exists($kpi->id, $scores)) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => 'Score KPI ' . $kpi->id . ' tidak terkirim.',
                    ];
                }
            }
            foreach ($kpis as $kpi) {
                $score = (int)$scores[$kpi->id];
                $note = $notes[$kpi->id] ?? null;

                $value = $this->getOrCreateValue($kpi, $user, $tl, $period, $month);
                $value->evaluator_id = $tl->id;
                $value->division_id = $user->division_id;
                $value->score = $score;
                $value->note = $note;
                $value->is_submitted = true;
                $value->save();
            }

            DB::commit();
            return [
                'success' => true,
                'message' => 'Penilaian bulanan berhasil disubmit.',
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return [
                'success' => false,
                'message' => config('app.debug')
                    ? 'Error: ' . $e->getMessage()
                    : 'Gagal menyimpan penilaian. Silakan coba lagi.',
            ];
        }
    }
}
