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
        Schema::create('diagram_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diagram_id')->index('diagram_views_diagram_id_index')->constrained('diagram', indexName: 'diagram_views_diagram_id_foreign')->onDelete('cascade');
            $table->foreignId('user_id')->index('diagram_views_user_id_index')->constrained('users', indexName: 'diagram_views_user_id_foreign')->onDelete('cascade');
            $table->timestamp('last_viewed_at')->useCurrent();
            $table->timestamps();

            $table->unique(['diagram_id', 'user_id']);

            /** indexes */
            $table->index('last_viewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagram_views');
    }
};
