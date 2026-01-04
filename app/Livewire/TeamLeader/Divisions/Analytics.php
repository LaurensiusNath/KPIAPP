<?php

namespace App\Livewire\TeamLeader\Divisions;

use App\Models\Division;
use App\Models\Period;
use App\Services\PeriodService;
use App\Services\TeamLeader\TeamLeaderAnalyticsContextService;
use App\Services\TeamLeader\TeamLeaderDashboardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teamLeader')]
class Analytics extends Component
{
    public ?Division $division = null;
    public ?Period $period = null;
    public ?int $month = null;
    public array $monthOptions = [];
    public string $selectedMonthLabel = '';

    // Monthly Performance Chart Data
    public array $monthlyPerformance = [];

    // Evaluation Status
    public array $evaluationStatus = [];

    // Appraisal Status
    public array $appraisalStatus = [];

    // Top Performers
    public array $topPerformers = [];

    // Staff Summary
    public array $staffSummary = [];

    // Division Stats
    public array $divisionStats = [];

    protected $queryString = [
        'month' => ['except' => null],
    ];

    public function mount(
        PeriodService $periodService,
        TeamLeaderDashboardService $dashboardService,
        TeamLeaderAnalyticsContextService $contextService,
    ): void {
        $leader = Auth::user();
        if (!$leader) {
            abort(403);
        }

        $this->division = $contextService->getLeaderDivision($leader);
        $this->period = $contextService->getActivePeriod($periodService);

        if (!$this->division) {
            session()->flash('error', 'Anda belum terdaftar sebagai leader pada divisi manapun.');
            $this->redirectRoute('tl.members');
            return;
        }

        if (!$this->period) {
            session()->flash('error', 'Belum ada periode aktif untuk dashboard.');
            $this->redirectRoute('tl.members');
            return;
        }

        $months = $dashboardService->getMonthsForPeriod($this->period);
        $this->monthOptions = $contextService->buildMonthOptions($this->period, $months);

        $requestedMonth = (int) ($this->month ?? request()->integer('month'));
        $this->month = $contextService->resolveMonth($months, $requestedMonth);

        $this->loadDashboardData($dashboardService);
    }

    public function updatedMonth(): void
    {
        $dashboardService = app(TeamLeaderDashboardService::class);
        $contextService = app(TeamLeaderAnalyticsContextService::class);

        $validMonths = array_column($this->monthOptions, 'value');
        $this->month = $contextService->coerceMonth((int) $this->month, $validMonths);

        $this->loadDashboardData($dashboardService);
    }

    protected function loadDashboardData(TeamLeaderDashboardService $dashboardService): void
    {
        if (!$this->period || !$this->division || !$this->month) {
            return;
        }

        $this->monthlyPerformance = $dashboardService->getMonthlyTeamPerformance($this->division, $this->period);
        $this->evaluationStatus = $dashboardService->getMonthlyEvaluationStatus($this->division, $this->period, $this->month);
        $this->appraisalStatus = $dashboardService->getAppraisalStatus($this->division, $this->period);
        $this->topPerformers = $dashboardService->getTopPerformers($this->division, $this->period, $this->month);
        $this->staffSummary = $dashboardService->getStaffSummary($this->division, $this->period, $this->month);
        $this->divisionStats = $dashboardService->getDivisionStats($this->division, $this->period, $this->month);
        $this->selectedMonthLabel = Carbon::create($this->period->year, $this->month, 1)->translatedFormat('F');

        $this->dispatch('team-chart-updated', data: $this->monthlyPerformance);
    }

    public function downloadReport()
    {
        if (!$this->period || !$this->division || !$this->month) {
            session()->flash('error', 'Data tidak lengkap untuk download laporan.');
            return;
        }

        $data = [
            'division' => $this->division,
            'period' => $this->period,
            'evaluationStatus' => $this->evaluationStatus,
            'appraisalStatus' => $this->appraisalStatus,
            'topPerformers' => $this->topPerformers,
            'staffSummary' => $this->staffSummary,
            'divisionStats' => $this->divisionStats,
            'monthLabel' => Carbon::create($this->period->year, $this->month, 1)->translatedFormat('F Y'),
        ];

        $pdf = Pdf::loadView('pdf.team-leader.dashboard.index', $data);
        $filename = sprintf('Dashboard-Divisi-%s-%s.pdf', $this->division->name, $data['monthLabel']);

        return response()->streamDownload(fn() => print($pdf->output()), $filename);
    }

    public function render()
    {
        if (!$this->division || !$this->period) {
            return redirect()->route('tl.members');
        }

        return view('livewire.team-leader.divisions.analytics');
    }
}
