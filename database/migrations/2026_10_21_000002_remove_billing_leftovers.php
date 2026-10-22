<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CraftDB Local (F3): remove what Stripe/Cashier billing and public share links left behind.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['stripe_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at']);
        });

        Schema::table('diagram', function (Blueprint $table) {
            $table->dropUnique(['public_token']);
        });

        Schema::table('diagram', function (Blueprint $table) {
            $table->dropColumn('public_token');
        });
    }

    /**
     * Approximate inverse (Cashier subscription tables are not restored) — enough for rollback in dev.
     */
    public function down(): void
    {
        Schema::table('diagram', function (Blueprint $table) {
            $table->uuid('public_token')->nullable()->unique();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_id')->nullable()->index();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();
        });
    }
};
