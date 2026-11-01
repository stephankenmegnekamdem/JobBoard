<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\TeamRole;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DatabaseSeeder extends Seeder
{
    /**
     * Safe to run repeatedly: accounts are looked up by email, and sample
     * companies and applications are only created when none exist yet.
     *
     * Model events are NOT disabled here. The Team slug is generated in a
     * "creating" hook, and disabling events would leave slugs empty.
     */
    public function run(): void
    {
        $seeker = $this->account('test@example.com', 'Test', AccountType::Seeker);
        $companyOwner = $this->account('company@example.com', 'Company', AccountType::Company);
        $this->admin('admin@example.com', 'Admin');

        if (Team::where('is_personal', false)->doesntExist()) {
            $companies = $this->seedCompanies($companyOwner);
            $this->seedListings($companies);
            $this->seedInvitation($companies->first(), $companyOwner, $seeker);
        }

        if (JobApplication::doesntExist()) {
            $this->seedApplications($seeker);
        }
    }

    private function account(string $email, string $firstName, AccountType $type): User
    {
        return User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $firstName,
                'last_name' => 'Example',
                'password' => 'password',
                'account_type' => $type,
                'email_verified_at' => now(),
            ],
        );
    }

    /**
     * is_admin is not mass-assignable, so it is set explicitly.
     */
    private function admin(string $email, string $firstName): User
    {
        $user = $this->account($email, $firstName, AccountType::Seeker);

        if (! $user->is_admin) {
            $user->forceFill(['is_admin' => true])->save();
        }

        return $user;
    }

    /**
     * @return Collection<int, Team>
     */
    private function seedCompanies(User $companyOwner): Collection
    {
        $names = ['Acme Ltd', 'Northwind Schools', 'Harbour Clinic'];

        $companies = collect($names)->map(function (string $name, int $index) use ($companyOwner) {
            $team = Team::factory()->create(['name' => $name]);

            $owner = $index === 0
                ? $companyOwner
                : User::factory()->create(['account_type' => AccountType::Company]);

            $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

            return $team;
        });

        // The company test account starts on its own company.
        $companyOwner->switchTeam($companies->first());

        return $companies;
    }

    /**
     * @param  Collection<int, Team>  $companies
     */
    private function seedListings(Collection $companies): void
    {
        $companies->each(function (Team $company) {
            $contact = $company->owner();

            JobListing::factory(3)->open()->create([
                'team_id' => $company->id,
                'contact_id' => $contact->id,
            ]);

            JobListing::factory()->expired()->create([
                'team_id' => $company->id,
                'contact_id' => $contact->id,
            ]);

            JobListing::factory()->unpublished()->create([
                'team_id' => $company->id,
                'contact_id' => $contact->id,
            ]);
        });
    }

    /**
     * A pending invitation for the seeker, so the dashboard has something to show.
     */
    private function seedInvitation(Team $company, User $inviter, User $seeker): void
    {
        TeamInvitation::firstOrCreate(
            ['team_id' => $company->id, 'email' => $seeker->email],
            [
                'invited_by' => $inviter->id,
                'role' => TeamRole::Member,
                'expires_at' => now()->addDays(3),
            ],
        );
    }

    private function seedApplications(User $seeker): void
    {
        $listings = JobListing::open()->get();

        JobApplication::factory(12)->recycle($listings)->create();

        // One application linked to the seeker account.
        JobApplication::factory()->create([
            'job_listing_id' => $listings->first()->id,
            'user_id' => $seeker->id,
            'name' => $seeker->name,
            'email' => $seeker->email,
        ]);
    }
}
