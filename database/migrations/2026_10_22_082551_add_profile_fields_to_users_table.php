<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->after('name');

            $table->string('tel_num')->nullable();

            $table->string('address')->nullable();

            $table->string('image')->nullable();

            $table->string('cv')->nullable();

            $table->text('about')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([

                'last_name',

                'tel_num',

                'address',

                'image',

                'cv',
                
                'about',
            ]);
        });
    }
};
