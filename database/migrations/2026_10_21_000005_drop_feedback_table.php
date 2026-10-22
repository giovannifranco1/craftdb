<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('feedback');
    }

    public function down(): void
    {
        // Irreversible: the "Contact Us" form was removed from the project.
    }
};
