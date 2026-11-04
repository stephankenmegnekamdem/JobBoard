<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\ApplicationsController;
use App\Http\Controllers\Company\ApplicationController as CompanyApplicationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use App\Support\HomeRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'jobs/index')->name('home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::get('applications/{application}', [CompanyApplicationController::class, 'show'])
            ->name('company.applications.show');

        Route::get('applications/{application}/cv', [CompanyApplicationController::class, 'cv'])
            ->name('company.applications.cv');

        // Only the company owner and its admins may change an application's status.
        Route::patch('applications/{application}/status', [CompanyApplicationController::class, 'updateStatus'])
            ->middleware(EnsureTeamMembership::class.':admin')
            ->name('company.applications.status');
    });

Route::middleware(['auth', 'verified'])
    ->get('applications', ApplicationsController::class)
    ->name('applications');

Route::middleware(['auth', 'verified'])
    ->get('account/home', function (Request $request) {
        return redirect(HomeRoute::for($request->user()));
    })
    ->name('account.home');

Route::middleware(['auth'])->group(function () {
    Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])
        ->name('invitations.accept');

    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])
        ->name('invitations.decline');
});

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('admin', AdminDashboardController::class)->name('admin.index');
});

require __DIR__.'/settings.php';
