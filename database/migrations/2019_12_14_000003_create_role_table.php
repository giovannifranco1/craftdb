<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('role', static function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name')->nullable();
            $table->string('description')->nullable();
        });

        DB::table('role')->insert([
            ['id' => 1, 'code' => 'editor', 'name' => 'Editor', 'description' => 'Can create, edit and view diagrams'],
            ['id' => 2, 'code' => 'viewer', 'name' => 'Viewer', 'description' => 'Can only view diagrams'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role');
    }
};
