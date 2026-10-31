<?php

namespace App\Actions\Fortify;

use App\Actions\Teams\CreateTeam;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\AccountType;
use App\Models\User;
use App\Rules\TeamName;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(private CreateTeam $createTeam)
    {
        //
    }

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string|null>  $input
     */
    public function create(array $input): User
    {
        $isInvited = filled($input['invitation'] ?? null);
        $isCompany = ($input['account_type'] ?? null) === AccountType::Company->value;

        $validated = Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'account_type' => ['required', Rule::enum(AccountType::class)],
            'company_name' => [
                Rule::requiredIf($isCompany && ! $isInvited),
                'string',
                'max:255',
                new TeamName,
            ],
            'invitation' => ['nullable', 'string', 'max:64'],
        ])->validate();

        $accountType = AccountType::from($validated['account_type']);

        return DB::transaction(function () use ($validated, $accountType, $isInvited) {
            $user = User::create([
                'name' => $validated['name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'account_type' => $accountType,
            ]);

            if ($accountType === AccountType::Company && ! $isInvited) {
                $this->createTeam->handle($user, $validated['company_name'], isPersonal: false);
            }

            return $user;
        });
    }
}
