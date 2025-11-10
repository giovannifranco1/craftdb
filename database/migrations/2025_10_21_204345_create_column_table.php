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
        Schema::create('column', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('table', indexName: 'table_id_foreign');
            $table->string('name');
            $table->string('type');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_foreign')->default(false);
            $table->boolean('is_nullable')->default(false);
            $table->integer('order');
            $table->integer('length')->nullable();
            $table->string('default_value')->nullable();
            $table->text('comment')->nullable();

            /** timestamps */
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('columns');
    }
};
