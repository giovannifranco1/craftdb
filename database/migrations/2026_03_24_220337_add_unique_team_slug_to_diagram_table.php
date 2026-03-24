<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diagram', function (Blueprint $table) {
            $table->unique(['team_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('diagram', function (Blueprint $table) {
            $table->dropUnique(['team_id', 'slug']);
        });
    }
};
