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
        Schema::create('note', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diagram_id')->constrained('diagram', indexName: 'note_diagram_id_foreign');
            $table->string('name')->nullable();
            $table->text('content')->nullable();
            $table->integer('x')->comment('position x in diagram');
            $table->integer('y')->comment('position y in diagram');
            $table->integer('width')->default(300)->comment('width of the note');
            $table->integer('height')->default(200)->comment('height of the note');
            $table->string('color')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note');
    }
};
