<?php

use App\Http\Controllers\Api\JobApplicationController;
use App\Http\Controllers\Api\JobListingController;
use Illuminate\Support\Facades\Route;

Route::get('jobs', [JobListingController::class, 'index'])->name('api.jobs.index');
Route::get('jobs/{jobListing}', [JobListingController::class,    'show'])->name('api.jobs.show');
Route::post('jobs/{jobListing}/applications', [JobApplicationController::class,    'store'])->name('api.jobs.applications.store');
