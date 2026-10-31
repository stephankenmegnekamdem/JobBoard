<?php

namespace App\Http\Controllers\Api\Admin;

use App\Actions\Teams\CreateTeam;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use App\Rules\TeamName;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class CompanyController extends Controller
{
    public function index(): JsonResource
    {
        return JsonResource::collection(Team::where('is_personal', false)->latest()->paginate(15));
    }

    public function store(Request $request, CreateTeam $createTeam): JsonResponse
    {
        $data = $this->validated($request);

        $owner = User::findOrFail($request->validate([
            'owner_id' => ['required', 'integer', Rule::exists('users', 'id')],
        ])['owner_id']);

        $company = DB::transaction(function () use ($createTeam, $owner, $data) {
            $team = $createTeam->handle($owner, $data['name'], isPersonal: false);

            $team->update(collect($data)->except('name')->all());

            return $team;
        });

        return response()->json(['data' => $company], 201);
    }

    public function show(Team $company): array
    {
        return ['data' => $company->load('members')];
    }

    public function update(Request $request, Team $company): array
    {
        abort_if($company->is_personal, 403, 'Personal teams cannot be edited.');

        $company->update($this->validated($request));

        return ['data' => $company];
    }

    public function destroy(Team $company): Response
    {
        abort_if($company->is_personal, 403, 'Personal teams cannot be deleted.');

        DB::transaction(function () use ($company) {
            User::where('current_team_id', $company->id)
                ->each(fn (User $user) => $user->switchToFallbackTeam($company));

            $company->invitations()->delete();
            $company->memberships()->delete();
            $company->delete();
        });

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', new TeamName],
            'location' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
