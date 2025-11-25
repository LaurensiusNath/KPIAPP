<?php

namespace App\Livewire\TeamLeader\Kpi;

use App\Models\Period;
use App\Models\User;
use App\Services\Exceptions\DomainValidationException;
use App\Services\Exceptions\PeriodClosedException;
use App\Services\Exceptions\UnauthorizedException;
use App\Services\KpiService;
use App\Services\PeriodService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teamLeader')]
class PlanForm extends Component
{
    public User $user;
    public ?Period $activePeriod = null;

    /** @var array<int, array{title:string, weight:string|float|null, scale:array<int,string>}> */
    public array $items = [];

    public float $totalWeight = 0.0;

    public function mount(User $user, PeriodService $periodService, KpiService $kpiService): void
    {
        $actor = Auth::user();
        if (!$actor) abort(403);

        if ($actor->division_id === null || $actor->division_id !== $user->division_id) {
            abort(403, 'User not in your division');
        }

        $this->user = $user;
        $this->activePeriod = $periodService->getActivePeriod();
        if (!$this->activePeriod) {
            abort(403, 'No active period available');
        }
        if (!$periodService->isCurrentWindowForKpiCreation($this->activePeriod)) {
            abort(403, 'KPI can only be created during the creation window (Jan 1-10 or Jul 1-10).');
        }

        // Prefill with existing KPIs if any, otherwise start with one blank row
        $existing = $kpiService->getKpisByUserAndPeriod($user, $this->activePeriod);
        if ($existing->count() > 0) {
            $this->items = $existing->map(function ($kpi) {
                $rawScale = is_array($kpi->criteria_scale) ? $kpi->criteria_scale : [];
                $normScale = [];
                for ($lvl = 1; $lvl <= 5; $lvl++) {
                    $normScale[$lvl] = (string)($rawScale[$lvl] ?? ($rawScale[(string)$lvl] ?? ''));
                }
                return [
                    'title' => (string) $kpi->title,
                    // ensure number input shows formatted value
                    'weight' => (string) number_format((float) $kpi->weight, 2, '.', ''),
                    'scale' => $normScale,
                ];
            })->values()->all();
        } else {
            $this->addRow();
        }
        $this->recomputeTotal();
    }

    public function addRow(): void
    {
        $this->items[] = [
            'title' => '',
            'weight' => '',
            'scale' => [
                1 => 'Very Poor',
                2 => 'Poor',
                3 => 'Average',
                4 => 'Good',
                5 => 'Excellent',
            ],
        ];
    }

    public function removeRow(int $index): void
    {
        if (!isset($this->items[$index])) return;
        array_splice($this->items, $index, 1);
        $this->recomputeTotal();
    }

    public function updatedItems(): void
    {
        $this->recomputeTotal();
    }

    private function recomputeTotal(): void
    {
        $sum = 0.0;
        foreach ($this->items as $row) {
            $w = (float)($row['weight'] ?? 0);
            $sum += $w;
        }
        $this->totalWeight = $sum;
    }

    public function submit(KpiService $service): void
    {
        // Basic validation
        $clean = [];
        foreach ($this->items as $i => $row) {
            $title = trim((string)($row['title'] ?? ''));
            $weight = (float)($row['weight'] ?? 0);
            $scale = $row['scale'] ?? [];

            if ($title === '' || $weight <= 0) {
                $this->addError('form', 'Semua item harus memiliki judul dan bobot > 0.');
                return;
            }
            if (!is_array($scale)) $scale = [];
            $clean[] = [
                'title' => $title,
                'weight' => $weight,
                'criteria_scale' => $scale,
            ];
        }

        if (abs($this->totalWeight - 100.0) > 0.00001) {
            $this->addError('form', 'Total bobot harus tepat 100% sebelum submit. Total saat ini: ' . number_format($this->totalWeight, 2) . '%.');
            return;
        }

        try {
            $service->createKpiBulk($this->user, $this->activePeriod, $clean, Auth::user());
            session()->flash('success', 'KPI berhasil disimpan.');
            redirect()->route('tl.kpi.items', ['user' => $this->user->id]);
        } catch (UnauthorizedException | PeriodClosedException | DomainValidationException $e) {
            $this->addError('form', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.team-leader.kpi.plan-form');
    }
}
