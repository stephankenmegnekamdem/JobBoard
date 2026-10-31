<?php

namespace Database\Factories;

use App\Enums\TeamRole;
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
     * Relations are lazy, so passing team_id or contact_id creates nothing extra.
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
            'published_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ];
    }

    /**
     * Make sure the contact belongs to the company, so the listing satisfies
     * the rule that a contact must be a member of the company.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (JobListing $listing) {
            $company = $listing->company;
            $contact = $listing->contact;

            if (! $company || ! $contact || $company->members()->whereKey($contact->id)->exists()) {
                return;
            }

            // The first member of a new company becomes its owner. Anyone
            // added after that joins as a member, so there is only one owner.
            $role = $company->members()->exists() ? TeamRole::Member : TeamRole::Owner;

            $company->members()->attach($contact, ['role' => $role->value]);
        });
    }

    public function open(): static
    {
        return $this->state(fn () => [
            'published_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['published_at' => null]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'published_at' => now()->subMonth(),
            'expires_at' => now()->subDay(),
        ]);
    }
}
