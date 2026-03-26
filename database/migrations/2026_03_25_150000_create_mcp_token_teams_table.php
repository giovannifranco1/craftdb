<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mcp_token_teams', function (Blueprint $table) {
            $table->id();
            $table->string('token_id');
            $table->unsignedBigInteger('team_id');
            $table->timestamp('created_at')->useCurrent();

            $table->index('token_id');
            $table->unique(['token_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mcp_token_teams');
    }
};
