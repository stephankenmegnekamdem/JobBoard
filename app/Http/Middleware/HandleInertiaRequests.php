<?php

namespace App\Http\Middleware;

use App\Data\UserTeam;
use App\Models\User;
use App\Support\HomeRoute;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            // The single source of truth for "where is this user's home".
            // Header, sidebar, and login all use this value.
            'homeUrl' => fn () => $user ? HomeRoute::for($user) : '/',
            'currentTeam' => fn () => $this->currentCompanyTeam($user),
            'teams' => fn () => $user?->toUserTeams(includeCurrent: true) ?? [],
        ];
    }

    private function currentCompanyTeam(?User $user): ?UserTeam
    {
        $team = $user?->currentTeam;

        if (! $team || $team->is_personal || ! $user->belongsToTeam($team)) {
            return null;
        }

        return $user->toUserTeam($team);
    }
}
