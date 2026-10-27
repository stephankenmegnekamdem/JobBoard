<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyController extends Controller
{
    public function index()
    {
        return JsonResource::collection(Team::latest()->paginate(15));
    }

    public function store(Request $request)
    {
        $company = Team::create($this->validated($request));

        return response()->json(['data' => $company], 201);
    }

    public function show(Team $company)
    {
        return ['data' => $company->load('members')];
    }

    public function update(Request $request, Team $company)
    {
        $company->update($this->validated($request));

        return ['data' => $company];
    }

    public function destroy(Team $company)
    {
        $company->delete();

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string'], ]);
    }
}
