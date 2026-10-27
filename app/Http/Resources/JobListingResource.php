<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobListingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id,    'title' => $this->title,    'short_description' => $this->short_description,    'location' => $this->location,    'salary' => $this->salary,    'working_time' => $this->working_time,    'published_at' => $this->published_at,    'company' => ['id' => $this->company->id, 'name' => $this->company->name]];
    }
}
