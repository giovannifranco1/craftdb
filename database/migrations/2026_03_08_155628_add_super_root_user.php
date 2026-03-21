<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations. Inserts super root user (id 1) if it doesn't exist.
     * Team is created in ensure_super_root_has_team migration.
     */
    public function up(): void
    {
        if (User::find(1) !== null) {
            return;
        }

        $email = env('SUPER_ROOT_EMAIL', 'superroot@craftdb.app');
        $password = Hash::make(env('SUPER_ROOT_PASSWORD', 'changeme'));
        $name = env('SUPER_ROOT_NAME', 'Super Root');

        DB::table('users')->insert([
            'id' => 1,
            'name' => $name,
            'email' => $email,
            'locale' => 'en',
            'password' => $password,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->where('id', 1)->delete();
    }
};
