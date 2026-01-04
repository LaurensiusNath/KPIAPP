<?php

use App\Http\Controllers\InitController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Admin\Appraisal\Form as AdminForm;
use App\Livewire\Admin\Appraisal\Index as AdminAppraisalIndex;
use App\Livewire\Admin\Appraisal\Divisions\Index as AppraisalDivisionsIndex;
use App\Livewire\Admin\Appraisal\Divisions\Show as AppraisalDivisionsShow;
use App\Livewire\Admin\Appraisal\Staff\Show as AppraisalStaffShow;
use App\Livewire\Admin\Dashboard\Index as AdminDashboard;
use App\Livewire\Admin\Divisions\Analytics as AdminDivisionAnalytics;
use App\Livewire\Admin\Divisions\Edit as AdminDivisionsEdit;
use App\Livewire\Admin\Divisions\Index as AdminDivisionsIndex;
use App\Livewire\Admin\Periods\Index as AdminPeriodsIndex;
use App\Livewire\Admin\Periods\Show as AdminPeriodsShow;
use App\Livewire\Admin\Users\Analytics as UserAnalytics;
use App\Livewire\Admin\Users\Index as AdminUsersIndex;


use App\Livewire\TeamLeader\Kpi\Items as TlKpiItems;
use App\Livewire\TeamLeader\Kpi\Members as TlKpiMembers;
use App\Livewire\TeamLeader\Kpi\PlanForm as TlKpiPlanForm;
use App\Livewire\TeamLeader\Kpi\Monthly as TlKpiMonthly;
use App\Livewire\TeamLeader\Kpi\History as TlKpiHistory;
use App\Livewire\TeamLeader\Divisions\Analytics as TlDivisionAnalytics;
use App\Livewire\TeamLeader\Members as TlMembers;
use App\Livewire\TeamLeader\Users\Analytics as TlUserAnalytics;

use App\Livewire\TeamLeader\Appraisal\Form as TlForm;
use App\Livewire\User\Dashboard\Index as UserDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', [InitController::class, 'initialize'])->name('init');


Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/dashboard', AdminDashboard::class)->name('admin.dashboard');
        Route::get('/users', AdminUsersIndex::class)->name('admin.users.index');
        Route::get('/users/create', AdminUsersIndex::class)->name('admin.users.create');
        Route::get('/users/{user}/edit', AdminUsersIndex::class)->name('admin.users.edit');
        Route::get('/users/{user:id}/analytics', UserAnalytics::class)->name('admin.users.analytics');
        Route::get('/divisions', AdminDivisionsIndex::class)->name('admin.divisions.index');
        Route::get('/divisions/create', AdminDivisionsIndex::class)->name('admin.divisions.create');
        Route::get('/divisions/{division:id}/edit', AdminDivisionsEdit::class)->name('admin.divisions.edit');
        Route::get('/divisions/{division:id}/analytics', AdminDivisionAnalytics::class)->name('admin.divisions.analytics');
        Route::get('/periods', AdminPeriodsIndex::class)->name('admin.periods.index');
        Route::get('/periods/create', AdminPeriodsIndex::class)->name('admin.periods.create');
        // OPSIONAL detail page
        Route::get('/periods/{period}', AdminPeriodsShow::class)->name('admin.periods.show');
        // Appraisal 6 bulanan (HRD) - Hierarchical Structure
        Route::get('/appraisals/divisions', AppraisalDivisionsIndex::class)->name('admin.appraisals.divisions.index');
        Route::get('/appraisals/divisions/{division}/periods/{period}', AppraisalDivisionsShow::class)->name('admin.appraisals.divisions.show');
        Route::get('/appraisals/staff/{user}/periods/{period}', AppraisalStaffShow::class)->name('admin.appraisals.staff.show');
        Route::get('/appraisals', AdminAppraisalIndex::class)->name('admin.appraisals.index');
        Route::get('/appraisals/{user}/periods/{period}', AdminForm::class)->name('admin.appraisals.form');
    });

Route::middleware(['auth', 'verified', 'role:team-leader'])
    ->prefix('team-leader')
    ->group(function () {
        // Dashboard - Division Analytics
        Route::get('/dashboard', TlDivisionAnalytics::class)->name('tl.dashboard');
        // Employee list with KPI setup status (Manage KPI action)
        Route::get('/members', TlMembers::class)->name('tl.members');
        // KPI (items, planning, monthly evaluation, history)
        Route::prefix('kpi')->as('tl.kpis.')->group(function () {
            Route::get('/members', TlKpiMembers::class)->name('members');
            Route::get('/{user}/items', TlKpiItems::class)->name('items');
            Route::get('/{user}/plan', TlKpiPlanForm::class)->name('plan');
            Route::get('/{user}/monthly', TlKpiMonthly::class)->name('monthly');
            Route::get('/{user}/history', TlKpiHistory::class)->name('history');
        });
        Route::get('/users/{user:id}/analytics', TlUserAnalytics::class)->name('tl.user.analytics');
        // Appraisal 6 bulanan (Team Leader)
        Route::get('/appraisals/{user}', TlForm::class)->name('tl.appraisals.form');
    });

Route::middleware(['auth', 'verified', 'role:user'])
    ->prefix('user')
    ->group(function () {
        Route::get('/dashboard', UserDashboard::class)->name('user.dashboard');
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
