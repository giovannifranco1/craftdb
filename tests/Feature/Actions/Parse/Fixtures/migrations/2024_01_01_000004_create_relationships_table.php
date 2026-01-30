<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_user_id')->index('relationships_from_user_id_index')->constrained('users', indexName: 'relationships_from_user_id_foreign');
            $table->foreignId('to_user_id')->index('relationships_to_user_id_index')->constrained('users', indexName: 'relationships_to_user_id_foreign');
            $table->foreignId('from_post_id')->index('relationships_from_post_id_index')->constrained('posts', indexName: 'relationships_from_post_id_foreign');
            $table->foreignId('to_post_id')->index('relationships_to_post_id_index')->constrained('posts', indexName: 'relationships_to_post_id_foreign');
            $table->foreignId('related_comment_id')->nullable()->index('relationships_related_comment_id_index')->constrained('comments',  indexName: 'relationships_related_comment_id_foreign');
            $table->enum('type', ['1:1', '1:n', 'n:1']);
            $table->string('label')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relationships');
    }
};
