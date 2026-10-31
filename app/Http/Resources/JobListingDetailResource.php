<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobListingDetailResource extends JsonResource
{
    /**
     * The contact is shown by name only. Their email, phone, and account
     * details are never sent to the public.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'keywords' => $this->keywords,
            'location' => $this->location,
            'salary' => $this->salary,
            'working_time' => $this->working_time,
            'link' => $this->link,
            'image' => $this->image,
            'published_at' => $this->published_at,
            'expires_at' => $this->expires_at,
            'company' => $this->company === null ? null : [
                'id' => $this->company->id,
                'name' => $this->company->name,
                'location' => $this->company->location,
                'website' => $this->company->website,
                'description' => $this->company->description,
            ],
            'contact' => $this->contact === null ? null : [
                'name' => $this->contact->name,
            ],
        ];
    }
}
