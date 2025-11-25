<?php

namespace App\Livewire\TeamLeader\Users;

use App\Models\Period;
use App\Models\User;
use App\Services\PeriodService;
use App\Services\UserAnalyticsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teamLeader')]
class Analytics extends Component
{
    public User $user;
    public ?Period $period = null;
    public ?int $month = null;
    public array $monthOptions = [];
    public ?float $monthlyAverage = null;
    public array $kpiRows = [];
    public array $trendSeries = [];
    public string $selectedMonthLabel = '';

    protected $queryString = [
        'month' => ['except' => null],
    ];

    public function mount(PeriodService $periodService, User $user, UserAnalyticsService $analyticsService): void
    {
        $leader = Auth::user()?->loadMissing('leading');
        $division = $leader?->leading;

        if (!$division) {
            session()->flash('error', 'Anda belum terhubung dengan divisi manapun.');
            $this->redirectRoute('tl.members');
            return;
        }

        if ($user->division_id !== $division->id) {
            session()->flash('error', 'User tidak berada dalam divisi Anda.');
            $this->redirectRoute('tl.members');
            return;
        }

        $this->user = $user->load('division');
        $this->period = $periodService->getActivePeriod();

        if (!$this->period) {
            session()->flash('error', 'Belum ada periode aktif untuk analitik user.');
            $this->redirectRoute('tl.members');
            return;
        }

        $months = $analyticsService->getMonthsForPeriod($this->period);
        $this->monthOptions = collect($months)->map(function (int $value) {
            return [
                'value' => $value,
                'label' => Carbon::create($this->period->year, $value, 1)->translatedFormat('F'),
            ];
        })->toArray();

        $requested = (int) ($this->month ?? request()->integer('month'));
        $this->month = in_array($requested, $months, true)
            ? $requested
            : $this->resolveDefaultMonth($months);

        $this->loadAnalytics($analyticsService);
    }

    protected function resolveDefaultMonth(array $months): int
    {
        $current = now()->month;
        if (in_array($current, $months, true)) {
            return $current;
        }

        return end($months) ?: reset($months);
    }

    public function updatedMonth(): void
    {
        $analyticsService = app(UserAnalyticsService::class);
        $validMonths = array_column($this->monthOptions, 'value');
        if (!in_array((int) $this->month, $validMonths, true)) {
            $this->month = $validMonths[0] ?? now()->month;
        }

        $this->loadAnalytics($analyticsService);
    }

    protected function loadAnalytics(UserAnalyticsService $analyticsService): void
    {
        if (!$this->period || !$this->month) {
            return;
        }

        $this->monthlyAverage = $analyticsService->getUserMonthlyAverage($this->user, $this->period, $this->month);
        $this->kpiRows = $analyticsService->getUserMonthlyKpiBreakdown($this->user, $this->period, $this->month);
        $this->trendSeries = $analyticsService->getTrendSeries($this->user, $this->period);
        $this->selectedMonthLabel = Carbon::create($this->period->year, $this->month, 1)->translatedFormat('F');

        $this->dispatch('user-chart-updated', data: $this->trendSeries);
    }

    public function downloadReport()
    {
        if (!$this->period || !$this->month) {
            session()->flash('error', 'Data tidak lengkap untuk download laporan.');
            return;
        }

        $data = [
            'user' => $this->user,
            'period' => $this->period,
            'month' => $this->month,
            'monthLabel' => Carbon::create($this->period->year, $this->month, 1)->translatedFormat('F Y'),
            'monthlyAverage' => $this->monthlyAverage,
            'kpiRows' => $this->kpiRows,
            'trendSeries' => $this->trendSeries,
        ];

        $pdf = Pdf::loadView('pdf.team-leader.user-analytics', $data);
        $filename = sprintf('Laporan-User-%s-%s.pdf', $this->user->name, $data['monthLabel']);

        return response()->streamDownload(fn() => print($pdf->output()), $filename);
    }

    public function render()
    {
        if (!$this->period) {
            return redirect()->route('tl.members');
        }

        return view('livewire.admin.users.analytics');
    }
}
