<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CraftDB Local (F2): remove what auth, social login, invitations and roles left behind.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_user', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });

        Schema::dropIfExists('invitation');
        Schema::dropIfExists('role');
        Schema::dropIfExists('password_reset_tokens');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['github_id']);
            $table->dropUnique(['google_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at',
                'github_id', 'github_token', 'github_refresh_token',
                'google_id', 'google_token', 'google_refresh_token',
            ]);
        });
    }

    /**
     * Approximate inverse (original FK/index names and seeded roles are not restored) — enough for rollback in dev.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->string('github_id')->nullable()->unique();
            $table->text('github_token')->nullable();
            $table->text('github_refresh_token')->nullable();
            $table->string('google_id')->nullable()->unique();
            $table->text('google_token')->nullable();
            $table->text('google_refresh_token')->nullable();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('role', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name')->nullable();
            $table->string('description')->nullable();
        });

        Schema::create('invitation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('team');
            $table->foreignId('role_id')->constrained('role');
            $table->string('email');
            $table->string('token', 64);
            $table->timestamp('expires_at');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->unique(['team_id', 'email', 'token']);
        });

        Schema::table('team_user', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained('role');
        });
    }
};
