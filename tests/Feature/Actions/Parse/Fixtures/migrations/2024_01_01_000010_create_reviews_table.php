<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignUuid('product_id')->constrained('products');
            $table->tinyInteger('rating')->unsigned();
            $table->string('title', 200);
            $table->text('comment');
            $table->boolean('is_verified')->default(false);
            $table->integer('helpful_count')->default(0);
            $table->timestamps();
            
            // Unique constraint: one review per user per product
            $table->unique(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
