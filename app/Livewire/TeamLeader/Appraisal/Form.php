<?php

namespace App\Livewire\TeamLeader\Appraisal;

use App\Models\User;
use App\Models\Appraisal;
use App\Services\AppraisalService;
use App\Services\Exceptions\DomainValidationException;
use App\Services\PeriodService;
use App\Models\Period;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.teamLeader')]
class Form extends Component
{
    public User $user;
    public ?Period $period = null;
    public ?Appraisal $appraisal = null;
    public array $summary = [];
    public string $comment_teamleader = '';
    public bool $readonly = false;
    public string $statusBadge = '';
    public bool $submissionWindowOpen = false;
    public string $submissionWindowMessage = '';

    public function mount(User $user, AppraisalService $service, PeriodService $periodService)
    {
        $this->user = $user->load('division');

        $activePeriod = $periodService->getActivePeriod();

        if (!$activePeriod) {
            session()->flash('error', 'Belum ada periode aktif untuk appraisal.');
            $this->redirectRoute('tl.members');
            return;
        }

        $this->period = $activePeriod;
        $this->submissionWindowOpen = $periodService->isCurrentWindowForAppraisal($this->period);
        $this->submissionWindowMessage = $this->period->semester === 1
            ? 'Penilaian appraisal semester 1 dibuka 25-31 Juni.'
            : 'Penilaian appraisal semester 2 dibuka 25-31 Desember.';

        // Load appraisal if exists
        $this->appraisal = Appraisal::query()
            ->where('user_id', $this->user->id)
            ->where('period_id', $this->period->id)
            ->first();

        // Summary
        $this->summary = $service->getSemesterSummary($this->user->id, $this->period->id);

        if ($this->appraisal) {
            $this->comment_teamleader = $this->appraisal->comment_teamleader ?? '';
        }
        $this->computeStatus();
    }

    public function rules(): array
    {
        if ($this->readonly) return [];
        return [
            'comment_teamleader' => ['required', 'string', 'min:10'],
        ];
    }

    protected function computeStatus(): void
    {
        if ($this->appraisal?->is_finalized) {
            $this->statusBadge = 'Finalized';
            $this->readonly = true;
            return;
        }
        if (!$this->appraisal || !$this->appraisal->teamleader_submitted_at) {
            $this->statusBadge = 'Waiting for Team Leader';
            $this->readonly = false;
            return;
        }
        if ($this->appraisal->teamleader_submitted_at && !$this->appraisal->hrd_submitted_at) {
            $this->statusBadge = 'Waiting for HRD';
            $this->readonly = true; // TL cannot edit after submit
            return;
        }
        $this->statusBadge = 'Pending';
    }

    public function submit(AppraisalService $service)
    {
        if (!$this->period) {
            session()->flash('error', 'Periode aktif belum ditetapkan oleh admin.');
            return;
        }

        if (!$this->submissionWindowOpen) {
            session()->flash('error', 'Penilaian appraisal hanya dapat disubmit pada periode yang telah ditentukan admin.');
            return;
        }

        if ($this->readonly) {
            session()->flash('error', 'Form tidak dapat disubmit.');
            return;
        }
        $this->validate();
        try {
            $result = $service->saveTeamLeaderAppraisal($this->user->id, $this->period->id, [
                'comment_teamleader' => $this->comment_teamleader,
            ]);
            session()->flash($result['success'] ? 'success' : 'error', $result['message']);

            if ($result['success']) {
                return redirect()->route('tl.appraisal.form', ['user' => $this->user->id]);
            }

            // Refresh appraisal for non-success states (validation warnings, etc.)
            $this->appraisal = Appraisal::where('user_id', $this->user->id)
                ->where('period_id', $this->period->id)
                ->first();
            $this->computeStatus();
        } catch (DomainValidationException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Terjadi kesalahan menyimpan appraisal.');
        }
    }

    public function render()
    {
        if (!$this->period) {
            return redirect()->route('tl.members');
        }

        return view('livewire.team-leader.appraisal.form');
    }
}
