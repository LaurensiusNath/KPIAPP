<?php

namespace App\Livewire\Admin\Divisions;

use App\Models\Division;
use App\Models\Period;
use App\Services\DivisionAnalyticsService;
use App\Services\PeriodService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Analytics extends Component
{
    public Division $division;
    public ?Period $period = null;
    public ?int $month = null;
    public array $monthOptions = [];
    public ?float $divisionAverage = null;
    public array $userScores = [];
    public array $trendSeries = [];
    public string $selectedMonthLabel = '';

    protected $queryString = [
        'month' => ['except' => null],
    ];

    public function mount(
        Division $division,
        PeriodService $periodService,
        DivisionAnalyticsService $analyticsService
    ): void {
        $this->division = $division;
        $this->period = $periodService->getActivePeriod();

        if (!$this->period) {
            session()->flash('error', 'Belum ada periode aktif untuk divisional analytics.');
            $this->redirectRoute('admin.divisions.index');
            return;
        }

        $months = $analyticsService->getMonthsForPeriod($this->period);
        $this->monthOptions = collect($months)->map(function (int $value) {
            return [
                'value' => $value,
                'label' => Carbon::create($this->period->year, $value, 1)->translatedFormat('F'),
            ];
        })->toArray();

        $requestedMonth = (int) ($this->month ?? request()->integer('month'));
        $this->month = in_array($requestedMonth, $months, true)
            ? $requestedMonth
            : $this->resolveDefaultMonth($months);

        $this->loadAnalytics($analyticsService);
    }

    protected function resolveDefaultMonth(array $months): int
    {
        $currentMonth = now()->month;
        foreach ($months as $month) {
            if ($month === $currentMonth) {
                return $month;
            }
        }

        return end($months) ?: reset($months);
    }

    public function updatedMonth(): void
    {
        $analyticsService = app(DivisionAnalyticsService::class);
        $validMonths = array_column($this->monthOptions, 'value');
        if (!in_array((int) $this->month, $validMonths, true)) {
            $this->month = $validMonths[0] ?? now()->month;
        }
        $this->loadAnalytics($analyticsService);
    }

    protected function loadAnalytics(DivisionAnalyticsService $analyticsService): void
    {
        if (!$this->period) {
            return;
        }

        $this->divisionAverage = $analyticsService->getDivisionMonthlyAverage($this->division, $this->period, $this->month);
        $this->userScores = $analyticsService->getDivisionUserMonthlyScores($this->division, $this->period, $this->month)->toArray();
        $this->trendSeries = $analyticsService->getDivisionTrendSeries($this->division, $this->period);
        $this->selectedMonthLabel = Carbon::create($this->period->year, $this->month, 1)->translatedFormat('F');

        $this->dispatch('division-chart-updated', data: $this->trendSeries);
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
            'month' => $this->month,
            'monthLabel' => Carbon::create($this->period->year, $this->month, 1)->translatedFormat('F Y'),
            'divisionAverage' => $this->divisionAverage,
            'userScores' => $this->userScores,
            'trendSeries' => $this->trendSeries,
        ];

        $pdf = Pdf::loadView('pdf.admin.divisions.analytics.index', $data);
        $filename = sprintf('Laporan-Divisi-%s-%s.pdf', $this->division->name, $data['monthLabel']);

        return response()->streamDownload(fn() => print($pdf->output()), $filename);
    }

    public function render()
    {
        return view('livewire.admin.divisions.analytics');
    }
}
