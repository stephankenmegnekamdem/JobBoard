<?php

namespace Database\Factories;

use App\Models\JobApplication;
use App\Models\JobListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobApplication>
 */
class JobApplicationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_listing_id' => JobListing::factory(),
            'user_id' => null,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'tel_num' => fake()->phoneNumber(),
            'message' => fake()->paragraph(),
            'status' => JobApplication::STATUS_NEW,
            'emailed_at' => null,
        ];
    }
}
