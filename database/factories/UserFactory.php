<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'account_type' => AccountType::Seeker,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * A company account that owns a company team.
     */
    public function company(): static
    {
        return $this
            ->state(fn (array $attributes) => ['account_type' => AccountType::Company])
            ->afterCreating(function (User $user) {
                $team = Team::factory()->create([
                    'name' => $user->name."'s Company",
                ]);

                $team->members()->attach($user, [
                    'role' => TeamRole::Owner->value,
                ]);

                $user->switchTeam($team);
            });
    }

    /**
     * A platform administrator. Set with forceFill, because is_admin is not
     * mass-assignable, so it cannot be set through create() arguments.
     */
    public function admin(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->forceFill(['is_admin' => true])->save();
        });
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
