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
        Schema::create('diagram', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->index('team_id_foreign');
            $table->string('name');
            $table->string('slug');
            $table->string('style');
            $table->boolean('is_initial_load')->default(true);
            $table->text('description')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->string('database')->nullable();

            /** timestamps  */
            $table->timestamp('imported_at')->nullable()->comment('when the diagram was imported');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagram');
    }
};
