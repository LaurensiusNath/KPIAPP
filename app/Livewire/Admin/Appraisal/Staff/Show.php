<?php

namespace App\Livewire\Admin\Appraisal\Staff;

use App\Models\Period;
use App\Models\User;
use App\Services\AppraisalService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public User $user;
    public Period $period;
    public array $detail = [];

    public function mount(User $user, Period $period, AppraisalService $appraisalService): void
    {
        $this->user = $user->load('division');
        $this->period = $period;

        $this->detail = $appraisalService->getStaffAppraisalDetail($user, $period);
    }

    public function downloadReport()
    {
        if (!$this->period || !$this->user) {
            session()->flash('error', 'Data tidak lengkap untuk download laporan.');
            return;
        }

        // Refresh user data with division relation
        $user = User::with('division')->find($this->user->id);

        $data = [
            'user' => $user,
            'period' => $this->period,
            'detail' => $this->detail,
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.admin.appraisal.staff', $data);

        // Clear any view cache
        $pdf->setPaper('a4', 'landscape');

        $filename = sprintf(
            'Appraisal-Staff-%s-S%d-%d.pdf',
            $user->name,
            $this->period->semester,
            $this->period->year
        );

        return response()->streamDownload(fn() => print($pdf->output()), $filename);
    }

    public function render()
    {
        return view('livewire.admin.appraisal.staff.show');
    }
}
