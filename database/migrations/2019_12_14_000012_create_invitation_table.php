<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invitation', static function (Blueprint $table) {
            $table->id();

            $table->foreignId(config()->get('teams.foreign_keys.team_id', 'team_id'))
                ->index('invitation_team_id_index')
                ->constrained(config()->get('teams.tables.teams', 'team'), indexName: 'invitation_team_id_foreign');
            $table->foreignId('role_id')
                ->index('invitation_role_id_index')
                ->constrained(config()->get('teams.tables.roles', 'role'), indexName: 'invitation_role_id_foreign');

            $table->string('email');
            $table->timestamps();

            $table->unique([config()->get('teams.foreign_keys.team_id', 'team_id'), 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitation');
    }
};
