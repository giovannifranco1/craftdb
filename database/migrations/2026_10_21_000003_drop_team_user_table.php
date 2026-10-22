<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CraftDB Local (F4): workspaces are ownership-only, so the membership pivot goes away.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('team_user');
    }

    public function down(): void
    {
        Schema::create('team_user', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('team')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['team_id', 'user_id']);
        });
    }
};
