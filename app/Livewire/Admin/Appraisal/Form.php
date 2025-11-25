<?php

namespace App\Livewire\Admin\Appraisal;

use App\Models\User;
use App\Models\Period;
use App\Models\Appraisal;
use App\Services\AppraisalService;
use App\Services\Exceptions\DomainValidationException;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.admin')]
class Form extends Component
{
    public User $user;
    public Period $period;
    public ?Appraisal $appraisal = null;
    public array $summary = [];
    public string $comment_hrd = '';
    public bool $readonly = false;
    public string $statusBadge = '';

    public function mount(User $user, Period $period, AppraisalService $service)
    {
        $this->user = $user->load('division');
        $this->period = $period;

        $this->appraisal = Appraisal::query()
            ->where('user_id', $this->user->id)
            ->where('period_id', $this->period->id)
            ->first();

        $this->summary = $service->getSemesterSummary($this->user->id, $this->period->id);

        if ($this->appraisal) {
            $this->comment_hrd = $this->appraisal->comment_hrd ?? '';
        }
        $this->computeStatus();
    }

    public function rules(): array
    {
        if ($this->readonly) return [];
        return [
            'comment_hrd' => ['required', 'string', 'min:10'],
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
            $this->readonly = true; // HRD waits
            return;
        }
        if ($this->appraisal->teamleader_submitted_at && !$this->appraisal->hrd_submitted_at) {
            $this->statusBadge = 'Waiting for HRD';
            $this->readonly = false;
            return;
        }
        $this->statusBadge = 'Pending';
    }

    public function submit(AppraisalService $service)
    {
        if ($this->readonly) {
            session()->flash('error', 'Form tidak dapat disubmit.');
            return;
        }
        $this->validate();
        try {
            $result = $service->saveHrdAppraisal($this->user->id, $this->period->id, [
                'comment_hrd' => $this->comment_hrd,
            ]);
            if ($result['success']) {
                // Redirect ke index dengan flash agar status terlihat langsung
                return redirect()->route('admin.appraisal.index', ['period' => $this->period->id])
                    ->with('success', $result['message']);
            }
            // Tetap di halaman jika gagal agar bisa perbaiki input
            session()->flash('error', $result['message']);
        } catch (DomainValidationException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Terjadi kesalahan menyimpan appraisal.');
        }
    }

    public function render()
    {
        return view('livewire.admin.appraisal.form');
    }
}
