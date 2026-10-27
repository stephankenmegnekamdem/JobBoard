<?php

namespace Database\Factories;

use App\Models\JobListing;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobListing>
 */
class JobListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'contact_id' => User::factory(),
            'title' => fake()->jobTitle(),
            'short_description' => fake()->sentence(12),
            'description' => fake()->paragraphs(3, true),
            'keywords' => implode(',', fake()->words(4)),
            'location' => fake()->city(),
            'salary' => '$'.fake()->numberBetween(40, 120).'k',
            'working_time' => fake()->randomElement(['Full-time', 'Part-time', 'Internship']),
            'published_at' => now(),
        ];
    }
}
