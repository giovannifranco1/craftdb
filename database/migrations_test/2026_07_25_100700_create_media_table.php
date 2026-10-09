<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')->constrained('posts');
            $table->string('file_path', 255);
            $table->string('mime_type', 80)->nullable();
            $table->integer('size_kb')->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
