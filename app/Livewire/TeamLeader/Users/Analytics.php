<?php

namespace App\Livewire\TeamLeader\Users;

use App\Models\Period;
use App\Models\User;
use App\Services\PeriodService;
use App\Services\TeamLeader\TeamLeaderAnalyticsContextService;
use App\Services\UserAnalyticsService;
use App\Services\UserService;
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

    public function mount(
        PeriodService $periodService,
        User $user,
        UserAnalyticsService $analyticsService,
        TeamLeaderAnalyticsContextService $contextService,
        UserService $userService,
    ): void {
        $leader = Auth::user();
        if (!$leader) {
            abort(403);
        }

        $division = $contextService->getLeaderDivision($leader);

        if (!$division) {
            session()->flash('error', 'Anda belum terhubung dengan divisi manapun.');
            $this->redirectRoute('tl.members');
            return;
        }

        try {
            $contextService->ensureUserInDivision($user, $division);
        } catch (\App\Services\Exceptions\UnauthorizedException $e) {
            session()->flash('error', $e->getMessage());
            $this->redirectRoute('tl.members');
            return;
        }

        $this->user = $userService->loadDivision($user);
        $this->period = $contextService->getActivePeriod($periodService);

        if (!$this->period) {
            session()->flash('error', 'Belum ada periode aktif untuk analitik user.');
            $this->redirectRoute('tl.members');
            return;
        }

        $months = $analyticsService->getMonthsForPeriod($this->period);
        $this->monthOptions = $contextService->buildMonthOptions($this->period, $months);

        $requested = (int) ($this->month ?? request()->integer('month'));
        $this->month = $contextService->resolveMonth($months, $requested);

        $this->loadAnalytics($analyticsService);
    }

    public function updatedMonth(UserAnalyticsService $analyticsService, TeamLeaderAnalyticsContextService $contextService): void
    {
        $validMonths = array_column($this->monthOptions, 'value');
        $this->month = $contextService->coerceMonth((int) $this->month, $validMonths);

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

        $pdf = Pdf::loadView('pdf.team-leader.users.analytics.index', $data);
        $filename = sprintf('Laporan-User-%s-%s.pdf', $this->user->name, $data['monthLabel']);

        return response()->streamDownload(fn() => print($pdf->output()), $filename);
    }

    public function render()
    {
        if (!$this->period) {
            return redirect()->route('tl.members');
        }

        return view('livewire.team-leader.users.analytics');
    }
}
