<?php

namespace App\Http\Controllers\Teams;

use App\Actions\Teams\CreateTeam;
use App\Enums\TeamRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\DeleteTeamRequest;
use App\Http\Requests\Teams\SaveTeamRequest;
use App\Models\Membership;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('teams/index', [
            'teams' => $user->toUserTeams(includeCurrent: true),
        ]);
    }

    public function store(SaveTeamRequest $request, CreateTeam $createTeam): RedirectResponse
    {
        $team = $createTeam->handle($request->user(), $request->validated('name'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Team created.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }

    public function edit(Request $request, Team $team): Response
    {
        $user = $request->user();
        $permissions = $user->toTeamPermissions($team);

        // Pending invitations contain email addresses, so only people who can
        // manage invitations see them.
        $canManageInvitations = $permissions->canCreateInvitation || $permissions->canCancelInvitation;

        return Inertia::render('teams/edit', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
            ],
            'members' => $team->members()->get()->map(function (User $member) {
                /** @var Membership $membership */
                $membership = $member->getRelation('pivot');

                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'avatar' => $this->avatarUrl($member),
                    'role' => $membership->role->value,
                    'role_label' => $membership->role->label(),
                ];
            })->values(),
            'invitations' => $canManageInvitations
                ? $team->invitations()
                    ->whereNull('accepted_at')
                    ->get()
                    ->map(fn (TeamInvitation $invitation) => [
                        'code' => $invitation->code,
                        'email' => $invitation->email,
                        'role' => $invitation->role->value,
                        'role_label' => $invitation->role->label(),
                        'created_at' => $invitation->created_at->toISOString(),
                    ])
                    ->values()
                : [],
            'permissions' => $permissions,
            'availableRoles' => $this->assignableRolesFor($user, $team),
        ]);
    }

    public function update(SaveTeamRequest $request, Team $team): RedirectResponse
    {
        Gate::authorize('update', $team);

        $team = DB::transaction(function () use ($request, $team) {
            $team = Team::whereKey($team->id)->lockForUpdate()->firstOrFail();

            $team->update(['name' => $request->validated('name')]);

            return $team;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Team updated.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }

    public function switch(Request $request, Team $team): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->belongsToTeam($team) && ! $team->is_personal, 403);

        $user->switchTeam($team);

        return back();
    }

    public function leave(Request $request, Team $team): RedirectResponse
    {
        Gate::authorize('leave', $team);

        $user = $request->user();
        $wasCurrent = User::whereKey($user->id)
            ->where('current_team_id', $team->id)
            ->exists();

        $team->memberships()
            ->where('user_id', $user->id)
            ->delete();

        if ($wasCurrent) {
            $user->switchToFallbackTeam($team);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('You left the team ":name"', ['name' => $team->name])]);

        return to_route('teams.index');
    }

    public function destroy(DeleteTeamRequest $request, Team $team): RedirectResponse
    {
        $user = $request->user();
        $wasCurrent = User::whereKey($user->id)
            ->where('current_team_id', $team->id)
            ->exists();

        DB::transaction(function () use ($user, $team) {
            User::where('current_team_id', $team->id)
                ->where('id', '!=', $user->id)
                ->each(fn (User $affectedUser) => $affectedUser->switchToFallbackTeam($team));

            $team->invitations()->delete();
            $team->memberships()->delete();
            $team->delete();
        });

        if ($wasCurrent) {
            $user->switchToFallbackTeam($team);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Team deleted.')]);

        return to_route('teams.index');
    }

    /**
     * Roles the current user may assign. Only the owner can grant admin.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function assignableRolesFor(User $user, Team $team): array
    {
        $roles = TeamRole::assignable();

        if ($user->ownsTeam($team)) {
            return $roles;
        }

        return array_values(array_filter(
            $roles,
            fn (array $role) => $role['value'] !== TeamRole::Admin->value,
        ));
    }

    /**
     * Public URL for a member's profile image, or null if none is set.
     */
    private function avatarUrl(User $member): ?string
    {
        return $member->image
            ? Storage::disk('public')->url($member->image)
            : null;
    }
}
