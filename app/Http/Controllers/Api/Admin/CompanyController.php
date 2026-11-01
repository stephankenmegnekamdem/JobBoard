<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class CompanyController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return JsonResource::collection(Team::latest()->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $company = Team::create($this->validated($request));

        return response()->json(['data' => $company], 201);
    }

    public function show(Team $company): JsonResponse
    {
        return response()->json(['data' => $company->load('members')]);
    }

    public function update(Request $request, Team $company): JsonResponse
    {
        $company->update($this->validated($request));

        return response()->json(['data' => $company]);
    }

    public function destroy(Team $company): Response
    {
        $company->delete();

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
    }
}
