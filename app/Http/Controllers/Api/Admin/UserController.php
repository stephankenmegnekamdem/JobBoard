<?php

namespace App\Http\Controllers\Api\Admin;

use App\Actions\Teams\CreateTeam;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return JsonResource::collection(User::latest()->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $user = User::create($this->validated($request));

        // Every user needs a personal team or the app's sidebar has nothing to show.
        app(CreateTeam::class)->handle($user, $user->name."'s Team", isPersonal: true);

        return response()->json(['data' => $user], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json(['data' => $user]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $this->validated($request, $user);

        // A blank password means "keep the current one".
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json(['data' => $user]);
    }

    public function destroy(User $user): Response
    {
        $user->delete();

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'tel_num' => ['nullable', 'string', 'max:30'],
            'is_admin' => ['boolean'],
        ]);
    }
}
