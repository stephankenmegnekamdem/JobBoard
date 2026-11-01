<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\Team;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('admin/index', [
            'stats' => [
                'users' => User::count(),
                'companies' => Team::where('is_personal', false)->count(),
                'openJobs' => JobListing::open()->count(),
                'newApplications' => JobApplication::where('status', JobApplication::STATUS_NEW)->count(),
            ],
        ]);
    }
}
