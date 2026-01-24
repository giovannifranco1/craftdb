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

            $table->foreignId('team_id')->index('invitation_team_id_index')->constrained('team', indexName: 'invitation_team_id_foreign');
            $table->foreignId('role_id')->index('invitation_role_id_index')->constrained('role', indexName: 'invitation_role_id_foreign');
            $table->string('email');
            $table->string('token', 64);

            $table->timestamp('expires_at');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'email', 'token']);

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
