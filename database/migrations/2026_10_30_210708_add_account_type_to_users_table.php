<?php

use App\Enums\AccountType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_type')->default(AccountType::Seeker->value)->after('email');
        });

        // Existing owners of a company team become company accounts.
        DB::table('users')
            ->whereIn('id', DB::table('team_members')
                ->join('teams', 'teams.id', '=', 'team_members.team_id')
                ->where('team_members.role', 'owner')
                ->where('teams.is_personal', false)
                ->select('team_members.user_id'))
            ->update(['account_type' => AccountType::Company->value]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('account_type');
        });
    }
};
