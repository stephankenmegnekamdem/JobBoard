<?php

use App\Http\Controllers\Api\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\Api\Admin\JobApplicationController as AdminJobApplicationController;
use App\Http\Controllers\Api\Admin\JobListingController as AdminJobListingController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\JobApplicationController;
use App\Http\Controllers\Api\JobListingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API
|--------------------------------------------------------------------------
*/

Route::get('jobs', [JobListingController::class, 'index'])
    ->name('api.jobs.index');

Route::get('jobs/{jobListing}', [JobListingController::class, 'show'])
    ->name('api.jobs.show');

Route::post('jobs/{jobListing}/applications', [JobApplicationController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('api.jobs.applications.store');

/*
|--------------------------------------------------------------------------
| Admin API
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'admin'])
    ->name('api.admin.')
    ->group(function () {
        Route::apiResource('jobs', AdminJobListingController::class);

        Route::apiResource('companies', AdminCompanyController::class)
            ->parameters(['companies' => 'company:id']);

        Route::apiResource('users', AdminUserController::class);

        // Applications are created by the public form only, so admins
        // can list, view, update status, and delete them.
        Route::apiResource('applications', AdminJobApplicationController::class)
            ->except(['store']);
    });
