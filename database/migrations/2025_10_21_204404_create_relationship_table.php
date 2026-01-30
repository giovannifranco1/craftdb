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
            $table->foreignId('from_table_id')->index('relationship_from_table_id_index')->constrained('table', indexName: 'relationship_from_table_id_foreign');
            $table->foreignId('from_column_id')->index('relationship_from_column_id_index')->constrained('column', indexName: 'relationship_from_column_id_foreign');
            $table->foreignId('to_table_id')->index('relationship_to_table_id_index')->constrained('table', indexName: 'relationship_to_table_id_foreign');
            $table->foreignId('to_column_id')->index('relationship_to_column_id_index')->constrained('column', indexName: 'relationship_to_column_id_foreign');
            $table->foreignId('parent_n_n_pivot_id')->nullable()->index('relationship_parent_n_n_pivot_id_index')->constrained('table',  indexName: 'relationship_parent_n_n_pivot_id_foreign');
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
        Schema::dropIfExists('relationship');
    }
};
