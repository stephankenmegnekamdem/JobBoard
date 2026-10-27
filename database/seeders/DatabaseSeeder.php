<?php

namespace Database\Seeders;

use App\Enums\TeamRole;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        Team::factory(3)->create()->each(function (Team $company) {
            $contact = User::factory()->create();
            $company->members()->attach($contact, ['role' => TeamRole::Owner->value]);
            JobListing::factory(6)->create([
                'team_id' => $company->id,
                'contact_id' => $contact->id, ]);
        });
        JobApplication::factory(10)->recycle(JobListing::all())->create();
    }
}
