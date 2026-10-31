<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    protected $signature = 'app:make-admin {email : The email address of the user to promote}';

    protected $description = 'Grant platform admin access to an existing user';

    public function handle(): int
    {
        $email = strtolower($this->argument('email'));

        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user) {
            $this->error("No user found with the email {$email}.");

            return self::FAILURE;
        }

        if ($user->is_admin) {
            $this->info("{$user->email} is already a platform admin.");

            return self::SUCCESS;
        }

        $user->forceFill(['is_admin' => true])->save();

        $this->info("{$user->email} is now a platform admin.");

        return self::SUCCESS;
    }
}
