<?php

use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations. Ensures super root (user id 1) has a team.
     */
    public function up(): void
    {
        $user = User::find(1);

        if ($user && $user->ownedTeams()->count() === 0) {
            $name = env('SUPER_ROOT_NAME', 'Super Root');
            $user->ownedTeams()->save(Team::forceCreate([
                'user_id' => 1,
                'name' => $name . "'s Team",
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $user = User::find(1);
        if ($user) {
            $user->ownedTeams()->each(fn (Team $team) => $team->delete());
        }
    }
};
