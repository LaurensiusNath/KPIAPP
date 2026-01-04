<?php

namespace App\Livewire\Admin\Appraisal\Divisions;

use App\Models\Division;
use App\Models\Period;
use App\Services\AppraisalService;
use App\Services\DivisionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Division $division;
    public Period $period;
    public array $summary = [];
    public array $trendSeries = [];
    public array $staffList = [];
    public ?int $openDropdownId = null;

    public function mount(Division $division, Period $period, AppraisalService $appraisalService): void
    {
        if (!$period->exists) {
            session()->flash('error', 'Periode tidak ditemukan. Silakan pilih periode yang valid.');
            $this->redirectRoute('admin.appraisals.divisions.index');
            return;
        }

        $this->division = $division;
        $this->period = $period;

        $this->summary = $appraisalService->getDivisionAppraisalSummary($division, $period);
        $this->trendSeries = $appraisalService->getDivisionTrendSeries($division, $period);
        $this->staffList = $appraisalService->getStaffAppraisalList($division, $period);

        $this->dispatch('division-appraisal-chart-updated', data: $this->trendSeries);
    }

    public function downloadReport(DivisionService $divisionService)
    {
        if (!$this->period || !$this->division) {
            session()->flash('error', 'Data tidak lengkap untuk download laporan.');
            return;
        }

        // Refresh division data with leader relation
        $division = $divisionService->findDivisionById($this->division->id);

        $data = [
            'division' => $division,
            'period' => $this->period,
            'summary' => $this->summary,
            'trendSeries' => $this->trendSeries,
            'staffList' => $this->staffList,
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.admin.appraisals.divisions.show', $data);

        // Set landscape for better table display
        $pdf->setPaper('a4', 'landscape');

        $filename = sprintf(
            'Appraisal-Divisi-%s-S%d-%d.pdf',
            $division->name,
            $this->period->semester,
            $this->period->year
        );

        return response()->streamDownload(fn() => print($pdf->output()), $filename);
    }

    public function toggleDropdown(?int $staffId): void
    {
        $this->openDropdownId = $this->openDropdownId === $staffId ? null : $staffId;
    }

    public function render()
    {
        return view('livewire.admin.appraisals.divisions.show');
    }
}
