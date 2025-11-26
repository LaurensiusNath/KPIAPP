<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Kpi;
use App\Models\Period;
use App\Models\User;
use App\Services\Exceptions\DomainValidationException;
use App\Services\Exceptions\PeriodClosedException;
use App\Services\Exceptions\UnauthorizedException;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class KpiService
{
    public function __construct(
        private readonly PeriodService $periodService,
        private readonly DatabaseManager $db
    ) {}

    public function getKpisByUserAndPeriod(User $user, Period $period): Collection
    {
        return Kpi::query()
            ->where('user_id', $user->id)
            ->where('period_id', $period->id)
            ->orderBy('created_at')
            ->get();
    }

    public function getTotalWeightForUserPeriod(User $user, Period $period): float
    {
        return (float) Kpi::query()
            ->where('user_id', $user->id)
            ->where('period_id', $period->id)
            ->sum('weight');
    }

    public function createKpi(array $data, User $actor): Kpi
    {
        // Expected $data: ['user_id','period_id','title','weight','criteria_scale_json']
        $this->assertTeamLeaderForMember($actor, (int)($data['user_id'] ?? 0));

        $period = Period::findOrFail((int)$data['period_id']);
        if (!$this->periodService->isCurrentWindowForKpiCreation($period)) {
            throw new PeriodClosedException('Periode tidak dalam jendela pembuatan KPI.');
        }

        $targetUser = User::findOrFail((int)$data['user_id']);

        $criteriaScale = $this->decodeCriteriaScale((string)($data['criteria_scale_json'] ?? '[]'));

        $newWeight = (float)$data['weight'];
        $currentTotal = $this->getTotalWeightForUserPeriod($targetUser, $period);
        $resultingTotal = $currentTotal + $newWeight;

        // Relaxed rule: allow incremental adds as long as total does not exceed 100
        if ($resultingTotal > 100.0 + 0.00001) {
            $remaining = max(0.0, 100.0 - $currentTotal);
            throw new DomainValidationException('Total bobot tidak boleh melebihi 100%. Sisa bobot yang tersedia: ' . number_format($remaining, 2) . '%.');
        }

        return $this->db->transaction(function () use ($targetUser, $period, $data, $criteriaScale, $newWeight) {
            return Kpi::create([
                'user_id' => $targetUser->id,
                'period_id' => $period->id,
                'title' => (string)$data['title'],
                'weight' => $newWeight,
                'criteria_scale' => $criteriaScale,
            ]);
        });
    }

    public function updateKpi(Kpi $kpi, array $data, User $actor): Kpi
    {
        $this->assertTeamLeaderForMember($actor, $kpi->user_id);

        $period = $kpi->period()->firstOrFail();
        if (!$this->periodService->isCurrentWindowForKpiCreation($period)) {
            throw new PeriodClosedException('Periode tidak dalam jendela perubahan KPI.');
        }

        $criteriaScale = isset($data['criteria_scale_json'])
            ? $this->decodeCriteriaScale((string)$data['criteria_scale_json'])
            : $kpi->criteria_scale;

        $newWeight = isset($data['weight']) ? (float)$data['weight'] : (float)$kpi->weight;

        // Recompute total: sum of others + newWeight must equal 100
        $sumOthers = (float) Kpi::query()
            ->where('user_id', $kpi->user_id)
            ->where('period_id', $kpi->period_id)
            ->where('id', '!=', $kpi->id)
            ->sum('weight');

        $resultingTotal = $sumOthers + $newWeight;
        // Relaxed rule: allow update as long as total does not exceed 100
        if ($resultingTotal > 100.0 + 0.00001) {
            $remaining = max(0.0, 100.0 - $sumOthers);
            throw new DomainValidationException('Total bobot tidak boleh melebihi 100%. Sisa bobot yang tersedia: ' . number_format($remaining, 2) . '%.');
        }

        // It's okay if resultingTotal < 100; finalization will enforce exactly 100

        return $this->db->transaction(function () use ($kpi, $data, $newWeight, $criteriaScale) {
            $kpi->update([
                'title' => isset($data['title']) ? (string)$data['title'] : $kpi->title,
                'weight' => $newWeight,
                'criteria_scale' => $criteriaScale,
            ]);

            return $kpi->refresh();
        });
    }

    /**
     * Update existing KPI items for a user in a period WITHOUT deleting anything.
     * Use this when KPIs already exist and you just want to update them.
     * $items: array of ['id'=>int|null, 'title'=>string, 'weight'=>float, 'criteria_scale'=>array]
     * - If 'id' is provided: update that specific KPI
     * - If 'id' is null/missing: create new KPI
     */
    public function updateKpiBulk(User $targetUser, Period $period, array $items, User $actor)
    {
        $this->assertTeamLeaderForMember($actor, $targetUser->id);

        if (!$this->periodService->isCurrentWindowForKpiCreation($period)) {
            throw new PeriodClosedException('Periode tidak dalam jendela pembuatan KPI.');
        }

        if (empty($items)) {
            throw new DomainValidationException('Daftar KPI tidak boleh kosong.');
        }

        // Validate items and compute total
        $total = 0.0;
        $prepared = [];
        foreach ($items as $idx => $it) {
            $id = isset($it['id']) && $it['id'] ? (int)$it['id'] : null;
            $title = trim((string)($it['title'] ?? ''));
            $weight = (float)($it['weight'] ?? 0);
            $scale = $it['criteria_scale'] ?? null;

            if ($title === '' || $weight <= 0) {
                throw new DomainValidationException("Item #" . ($idx + 1) . " tidak valid (judul/weight).");
            }

            if (!is_array($scale)) {
                $scaleJson = (string)($it['criteria_scale_json'] ?? '');
                $scale = $this->decodeCriteriaScale($scaleJson);
            }

            $total += $weight;
            $prepared[] = [
                'id' => $id,
                'title' => $title,
                'weight' => $weight,
                'criteria_scale' => $scale,
            ];
        }

        if (abs($total - 100.0) > 0.00001) {
            throw new DomainValidationException('Total bobot harus tepat 100% untuk submit. Total saat ini: ' . number_format($total, 2) . '%.');
        }

        // Update/Create strategy in a transaction - NO DELETES
        return $this->db->transaction(function () use ($targetUser, $period, $prepared) {
            foreach ($prepared as $row) {
                if ($row['id']) {
                    // Update existing KPI by ID
                    $kpi = Kpi::query()
                        ->where('id', $row['id'])
                        ->where('user_id', $targetUser->id)
                        ->where('period_id', $period->id)
                        ->firstOrFail();

                    $kpi->update([
                        'title' => $row['title'],
                        'weight' => (float)$row['weight'],
                        'criteria_scale' => $row['criteria_scale'],
                    ]);
                } else {
                    // Create new KPI
                    Kpi::create([
                        'user_id' => $targetUser->id,
                        'period_id' => $period->id,
                        'title' => $row['title'],
                        'weight' => (float)$row['weight'],
                        'criteria_scale' => $row['criteria_scale'],
                    ]);
                }
            }

            return Kpi::query()
                ->where('user_id', $targetUser->id)
                ->where('period_id', $period->id)
                ->orderBy('created_at')
                ->get();
        });
    }

    /**
     * Create all KPI items for a user in a period (Initial setup).
     * Use this for FIRST TIME setup. Will delete all existing KPIs.
     * $items: array of ['title'=>string, 'weight'=>float, 'criteria_scale'=>array]
     */
    public function createKpiBulk(User $targetUser, Period $period, array $items, User $actor)
    {
        $this->assertTeamLeaderForMember($actor, $targetUser->id);

        if (!$this->periodService->isCurrentWindowForKpiCreation($period)) {
            throw new PeriodClosedException('Periode tidak dalam jendela pembuatan KPI.');
        }

        if (empty($items)) {
            throw new DomainValidationException('Daftar KPI tidak boleh kosong.');
        }

        // Validate items and compute total
        $total = 0.0;
        $prepared = [];
        foreach ($items as $idx => $it) {
            $title = trim((string)($it['title'] ?? ''));
            $weight = (float)($it['weight'] ?? 0);
            $scale = $it['criteria_scale'] ?? null;

            if ($title === '' || $weight <= 0) {
                throw new DomainValidationException("Item #" . ($idx + 1) . " tidak valid (judul/weight).");
            }

            if (!is_array($scale)) {
                // try decode if provided as JSON
                $scaleJson = (string)($it['criteria_scale_json'] ?? '');
                $scale = $this->decodeCriteriaScale($scaleJson);
            }

            $total += $weight;
            $prepared[] = [
                'title' => $title,
                'weight' => $weight,
                'criteria_scale' => $scale,
            ];
        }

        if (abs($total - 100.0) > 0.00001) {
            throw new DomainValidationException('Total bobot harus tepat 100% untuk submit massal. Total saat ini: ' . number_format($total, 2) . '%.');
        }

        // Check if any existing KPIs have values
        $existingKpisWithValues = Kpi::query()
            ->where('user_id', $targetUser->id)
            ->where('period_id', $period->id)
            ->whereHas('kpiValues')
            ->exists();

        if ($existingKpisWithValues) {
            throw new DomainValidationException(
                'Tidak dapat mengganti KPI karena sudah ada penilaian. Gunakan mode update untuk mengubah KPI yang ada.'
            );
        }

        // Replace all KPIs in a transaction
        return $this->db->transaction(function () use ($targetUser, $period, $prepared) {
            // Safe to delete because we checked no kpi_values exist
            Kpi::query()->where('user_id', $targetUser->id)->where('period_id', $period->id)->delete();

            foreach ($prepared as $row) {
                Kpi::create([
                    'user_id' => $targetUser->id,
                    'period_id' => $period->id,
                    'title' => $row['title'],
                    'weight' => (float)$row['weight'],
                    'criteria_scale' => $row['criteria_scale'],
                ]);
            }

            return Kpi::query()
                ->where('user_id', $targetUser->id)
                ->where('period_id', $period->id)
                ->orderBy('created_at')
                ->get();
        });
    }

    public function deleteKpi(Kpi $kpi, User $actor): bool
    {
        $this->assertTeamLeaderForMember($actor, $kpi->user_id);

        $period = $kpi->period()->firstOrFail();
        if (!$this->periodService->isCurrentWindowForKpiCreation($period)) {
            throw new PeriodClosedException('Periode tidak dalam jendela penghapusan KPI.');
        }

        // Check if this KPI has any values
        $hasValues = DB::table('kpi_values')->where('kpi_id', $kpi->id)->exists();

        if ($hasValues) {
            throw new DomainValidationException(
                "Tidak dapat menghapus KPI '{$kpi->title}' karena sudah memiliki penilaian. " .
                    "Silakan hapus penilaian terlebih dahulu."
            );
        }

        return (bool) $this->db->transaction(function () use ($kpi) {
            return $kpi->delete();
        });
    }

    private function assertTeamLeaderForMember(User $actor, int $targetUserId): void
    {
        if ($actor->role !== 'team-leader') {
            throw new UnauthorizedException('Hanya Team Leader yang boleh melakukan operasi ini.');
        }

        $target = User::findOrFail($targetUserId);

        if ($actor->division_id === null || $target->division_id === null || $actor->division_id !== $target->division_id) {
            throw new UnauthorizedException('Akses ditolak. Pengguna bukan anggota divisi Anda.');
        }
    }

    /**
     * Decode JSON string to array; must be valid JSON.
     * Expecting an array/object that describes the scale (e.g., keys 1..5).
     */
    private function decodeCriteriaScale(string $json): array
    {
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new DomainValidationException('Format criteria_scale tidak valid (harus JSON).');
        }
        // Optional: Ensure 1..5 keys exist
        // for ($i = 1; $i <= 5; $i++) {
        //     if (!array_key_exists((string)$i, $decoded) && !array_key_exists($i, $decoded)) {
        //         throw new DomainValidationException('criteria_scale harus memiliki skala 1..5.');
        //     }
        // }
        return $decoded;
    }
}
