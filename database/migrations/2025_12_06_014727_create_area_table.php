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
        Schema::create('area', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diagram_id')
                ->index('area_diagram_id_index')
                ->constrained('diagram', indexName: 'area_diagram_id_foreign');
            $table->string('name')->nullable();
            $table->integer('x')->comment('position x in diagram');
            $table->integer('y')->comment('position y in diagram');
            $table->integer('width')->default(400)->comment('width of the area');
            $table->integer('height')->default(300)->comment('height of the area');
            $table->string('color')->nullable();
            $table->boolean('is_solid')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area');
    }
};
