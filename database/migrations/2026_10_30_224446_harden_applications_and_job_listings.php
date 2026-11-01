<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->unique(['job_listing_id', 'user_id'], 'job_applications_listing_user_unique');
            $table->unique(['job_listing_id', 'email'], 'job_applications_listing_email_unique');
        });

        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
            $table->unsignedBigInteger('contact_id')->nullable()->change();
            $table->foreign('contact_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Fails if any listing has no contact. Assign contacts first.
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
            $table->unsignedBigInteger('contact_id')->nullable(false)->change();
            $table->foreign('contact_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropUnique('job_applications_listing_user_unique');
            $table->dropUnique('job_applications_listing_email_unique');
        });
    }
};
