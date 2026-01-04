<?php

namespace App\Livewire\TeamLeader\Kpi;

use App\Models\Period;
use App\Models\User;
use App\Services\Exceptions\DomainValidationException;
use App\Services\Exceptions\PeriodClosedException;
use App\Services\Exceptions\UnauthorizedException;
use App\Services\KpiService;
use App\Services\PeriodService;
use App\Services\TeamLeader\TeamLeaderKpiItemService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teamLeader')]
class PlanForm extends Component
{
    public User $user;
    public ?Period $activePeriod = null;
    public bool $hadExistingKpis = false;

    /** @var array<int, array{id:int|null, title:string, weight:string|float|null, scale:array<int,string>, deleted:bool}> */
    public array $items = [];

    public float $totalWeight = 0.0;

    public function mount(User $user, PeriodService $periodService, KpiService $kpiService, TeamLeaderKpiItemService $tlKpiItemService): void
    {
        $actor = Auth::user();
        if (!$actor) abort(403);

        try {
            $tlKpiItemService->ensureActorCanManageUser($actor, $user);
        } catch (UnauthorizedException $e) {
            abort(403, $e->getMessage());
        }

        $this->user = $user;
        $this->activePeriod = $tlKpiItemService->getActivePeriod($periodService);
        if (!$this->activePeriod) {
            abort(403, 'No active period available');
        }
        if (!$tlKpiItemService->isCreationWindowOpen($periodService, $this->activePeriod)) {
            abort(403, 'KPI can only be created during the creation window (Jan 1-10 or Jul 1-10).');
        }

        // Prefill with existing KPIs if any, otherwise start with one blank row
        $existing = $kpiService->getKpisByUserAndPeriod($user, $this->activePeriod);
        if ($existing->count() > 0) {
            $this->hadExistingKpis = true;
            $this->items = $tlKpiItemService->buildPlanItemsFromExistingKpis($existing);
        } else {
            $this->addRow();
        }
        $this->recomputeTotal();
    }

    public function addRow(): void
    {
        $this->items[] = app(TeamLeaderKpiItemService::class)->newPlanRow();
    }

    public function removeRow(int $index): void
    {
        if (!isset($this->items[$index])) return;

        // Mark as deleted instead of removing from array
        $this->items[$index]['deleted'] = true;
        $this->recomputeTotal();
    }

    public function updatedItems(): void
    {
        $this->recomputeTotal();
    }

    private function recomputeTotal(): void
    {
        $this->totalWeight = app(TeamLeaderKpiItemService::class)->calculateTotalWeight($this->items);
    }

    public function submit(KpiService $service): void
    {
        // Separate active items and deleted IDs
        $clean = [];
        $removedIds = [];

        foreach ($this->items as $i => $row) {
            // Collect IDs of deleted items
            if (!empty($row['deleted'])) {
                if (!empty($row['id'])) {
                    $removedIds[] = (int)$row['id'];
                }
                continue; // Skip deleted items
            }

            $id = isset($row['id']) && $row['id'] ? (int)$row['id'] : null;
            $title = trim((string)($row['title'] ?? ''));
            $weight = (float)($row['weight'] ?? 0);
            $scale = $row['scale'] ?? [];

            if ($title === '' || $weight <= 0) {
                $this->addError('form', 'Semua item harus memiliki judul dan bobot > 0.');
                return;
            }
            if (!is_array($scale)) $scale = [];

            $clean[] = [
                'id' => $id,
                'title' => $title,
                'weight' => $weight,
                'criteria_scale' => $scale,
            ];
        }

        if (abs($this->totalWeight - 100.0) > 0.00001) {
            $this->addError('form', 'Total bobot harus tepat 100% sebelum submit. Total saat ini: ' . number_format($this->totalWeight, 2) . '%.');
            return;
        }

        // Debug logging
        Log::info('PlanForm Submit Debug', [
            'user_id' => $this->user->id,
            'active_items' => $clean,
            'removed_ids' => $removedIds,
            'had_existing' => $this->hadExistingKpis,
        ]);

        try {
            // Use updateKpiBulk if there are existing KPIs, createKpiBulk for initial setup
            if ($this->hadExistingKpis) {
                $service->updateKpiBulk($this->user, $this->activePeriod, $clean, Auth::user(), $removedIds);
            } else {
                $service->createKpiBulk($this->user, $this->activePeriod, $clean, Auth::user());
            }

            session()->flash('success', 'KPI berhasil disimpan.');
            redirect()->route('tl.kpis.items', ['user' => $this->user->id]);
        } catch (UnauthorizedException | PeriodClosedException | DomainValidationException $e) {
            $this->addError('form', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.team-leader.kpi.plan-form');
    }
}
