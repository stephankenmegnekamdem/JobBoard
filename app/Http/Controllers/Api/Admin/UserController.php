<?php

namespace App\Http\Controllers\Api\Admin;

use App\Actions\Teams\CreateTeam;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return JsonResource::collection(User::latest()->paginate(15));
    }

    public function store(Request $request)
    {
        $user = User::create($this->validated($request));
        app(CreateTeam::class)->handle($user, $user->name."'s Team", isPersonal: true);

        return response()->json(['data' => $user], 201);
    }

    public function show(User $user)
    {
        return ['data' => $user];
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);

        return ['data' => $user];
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->noContent();
    }

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
