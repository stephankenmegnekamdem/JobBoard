<?php

namespace App\Support;

use App\Models\Team;
use App\Models\User;

final class HomeRoute
{
    public const ADMIN = '/admin';

    public const SEEKER = '/applications';

    public static function for(User $user): string
    {
        if ($user->is_admin) {
            return self::ADMIN;
        }

        $team = self::companyTeam($user);

        return $team ? "/{$team->slug}/dashboard" : self::SEEKER;
    }

    private static function companyTeam(User $user): ?Team
    {
        $current = $user->currentTeam;

        if ($current && ! $current->is_personal && $user->belongsToTeam($current)) {
            return $current;
        }

        return $user->teams()
            ->where('teams.is_personal', false)
            ->orderBy('teams.name')
            ->first();
    }
}
