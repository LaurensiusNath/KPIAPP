<?php

use App\Http\Controllers\InitController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Admin\Appraisal\Form as AdminForm;
use App\Livewire\Admin\Appraisal\Index as AdminAppraisalIndex;
use App\Livewire\Admin\Appraisal\Divisions\Index as AppraisalDivisionsIndex;
use App\Livewire\Admin\Appraisal\Divisions\Show as AppraisalDivisionsShow;
use App\Livewire\Admin\Appraisal\Staff\Show as AppraisalStaffShow;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Division;
use App\Livewire\Admin\Divisions;
use App\Livewire\Admin\Divisions\Analytics as DivisionAnalytics;
use App\Livewire\Admin\PeriodDetail;
use App\Livewire\Admin\Users;
use App\Livewire\Admin\Users\Analytics as UserAnalytics;


use App\Livewire\TeamLeader\Kpi\Items as TlKpiItems;
use App\Livewire\TeamLeader\Kpi\Members as TlKpiMembers;
use App\Livewire\TeamLeader\Kpi\PlanForm as TlKpiPlanForm;
use App\Livewire\TeamLeader\Kpi\Monthly as TlKpiMonthly;
use App\Livewire\TeamLeader\Kpi\History as TlKpiHistory;
use App\Livewire\TeamLeader\Division\Analytics as TlDivisionAnalytics;
use App\Livewire\TeamLeader\Members as TlMembers;
use App\Livewire\TeamLeader\Users\Analytics as TlUserAnalytics;

use App\Livewire\Admin\Periods as AdminPeriods;
use App\Livewire\TeamLeader\Appraisal\Form as TlForm;
use App\Livewire\User\Analytics as UserSelfAnalytics;
use Illuminate\Support\Facades\Route;

Route::get('/', [InitController::class, 'initialize'])->name('init');


Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('admin.dashboard');
        Route::get('/users', Users::class)->name('admin.users');
        Route::get('/user/{user:id}/analytics', UserAnalytics::class)->name('admin.user.analytics');
        Route::get('/divisions', Divisions::class)->name('admin.divisions');
        Route::get('/division/{division:id}', Division::class)->name('admin.division.detail');
        Route::get('/division/{division:id}/analytics', DivisionAnalytics::class)->name('admin.division.analytics');
        Route::get('/periods', AdminPeriods::class)->name('admin.periods');
        // OPSIONAL detail page
        Route::get('/period/{period}', PeriodDetail::class)->name('admin.period.detail');
        // Appraisal 6 bulanan (HRD) - Hierarchical Structure
        Route::get('/appraisal/divisions', AppraisalDivisionsIndex::class)->name('admin.appraisal.divisions.index');
        Route::get('/appraisal/divisions/{division}/periods/{period}', AppraisalDivisionsShow::class)->name('admin.appraisal.divisions.show');
        Route::get('/appraisal/staff/{user}/periods/{period}', AppraisalStaffShow::class)->name('admin.appraisal.staff.show');
        // Legacy appraisal routes (for backward compatibility)
        Route::get('/appraisals', AdminAppraisalIndex::class)->name('admin.appraisal.index');
        Route::get('/appraisal/{user}/period/{period}', AdminForm::class)->name('admin.appraisal.form');
    });

Route::middleware(['auth', 'verified', 'role:team-leader'])
    ->prefix('team-leader')
    ->group(function () {
        // Dashboard - Division Analytics
        Route::get('/dashboard', TlDivisionAnalytics::class)->name('tl.dashboard');
        // Employee list with KPI setup status (Manage KPI action)
        Route::get('/members', TlMembers::class)->name('tl.members');
        // Monthly evaluation and appraisal list
        Route::get('/kpi/members', TlKpiMembers::class)->name('tl.kpi.members');
        Route::get('/kpi/{user}/items', TlKpiItems::class)->name('tl.kpi.items');
        // Form to set KPI plan for a user -> direct from tl.members
        Route::get('/kpi/{user}/plan', TlKpiPlanForm::class)->name('tl.kpi.plan');
        // Monthly evaluation
        Route::get('/kpi/{user}/monthly', TlKpiMonthly::class)->name('tl.kpi.monthly');
        Route::get('/kpi/{user}/history', TlKpiHistory::class)->name('tl.kpi.history');
        Route::get('/user/{user:id}/analytics', TlUserAnalytics::class)->name('tl.user.analytics');
        // Appraisal 6 bulanan (Team Leader)
        Route::get('/appraisal/{user}', TlForm::class)->name('tl.appraisal.form');
    });

Route::middleware(['auth', 'verified', 'role:user'])
    ->prefix('user')
    ->group(function () {
        Route::get('/dashboard', UserSelfAnalytics::class)->name('user.dashboard');
    });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified', 'role:user'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
require __DIR__ . '/auth.php';
