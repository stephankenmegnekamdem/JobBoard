<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationsController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

Route::inertia('/', 'jobs/index')->name('home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
    });

Route::middleware(['auth', 'verified'])->get('applications', ApplicationsController::class)->name('applications');


Route::middleware(['auth', 'verified'])->get('account/home', function (\Illuminate\Http\Request $request) {
    return redirect(\App\Support\HomeRoute::for($request->user()));
})->name('account.home');

Route::middleware(['auth'])->group(function () {
    Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('admin', AdminDashboardController::class)->name('admin.index');
});

require __DIR__.'/settings.php';
