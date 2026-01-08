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
        Schema::create('table', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diagram_id')
                ->index('table_diagram_id_index')
                ->constrained('diagram', indexName: 'table_diagram_id_foreign');
            $table->string('name');
            $table->boolean('is_pivot')->default(false);
            $table->integer('x')->comment('position x in diagram');
            $table->integer('y')->comment('position y in diagram');
            $table->string('color')->nullable();

            /** timestamps */
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table');
    }
};
