<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\AccountType;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function index(): JsonResource
    {
        return JsonResource::collection(User::latest()->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $user = new User;
        $user->fill(collect($data)->except(['is_admin', 'password'])->all());
        $user->password = $data['password'];
        $user->forceFill(['is_admin' => $data['is_admin'] ?? false])->save();

        return response()->json(['data' => $user], 201);
    }

    public function show(User $user): array
    {
        return ['data' => $user];
    }

    public function update(Request $request, User $user): array
    {
        $data = $this->validated($request, $user);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $isAdmin = $data['is_admin'] ?? null;
        unset($data['is_admin']);

        $user->fill($data);

        if ($isAdmin !== null) {
            $user->forceFill(['is_admin' => $isAdmin]);
        }

        $user->save();

        return ['data' => $user];
    }

    public function destroy(User $user): Response
    {
        abort_if(
            $user->teams()
                ->wherePivot('role', 'owner')
                ->where('teams.is_personal', false)
                ->exists(),
            409,
            'This user owns a company. Transfer ownership or delete the company first.',
        );

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
            'address' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:2000'],
            'account_type' => ['sometimes', Rule::enum(AccountType::class)],
            'is_admin' => ['sometimes', 'boolean'],
        ]);
    }
}
