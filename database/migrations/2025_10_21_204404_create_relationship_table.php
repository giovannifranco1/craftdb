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
        Schema::create('relationship', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_table_id')->constrained('table', indexName: 'table_id_foreign');
            $table->foreignId('from_column_id')->constrained('column', indexName: 'column_id_foreign');
            $table->foreignId('to_table_id')->constrained('table', indexName: 'table_id_foreign');
            $table->foreignId('to_column_id')->constrained('column', indexName: 'column_id_foreign');
            $table->foreignId('parent_n_n_pivot_id')->nullable()->constrained('table',  indexName: 'parent_n_n_pivot_id_foreign');
            $table->enum('type', ['1:1', '1:n', 'n:1']);
            $table->string('label')->nullable();

            /** timestamps */
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relationships');
    }
};
