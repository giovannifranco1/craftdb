<?php

namespace App\Actions\Local;

use App\Actions\Team\CreateNewTeam;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EnsureLocalUser
{
    public function __construct(private CreateNewTeam $createNewTeam) {}

    /**
     * Ensure the local user exists, is verified and owns a workspace.
     * Never changes an existing user.
     *
     * @return array{user: User, created: bool}
     */
    public function exec(string $email): array
    {
        return DB::transaction(function () use ($email): array {
            $user = User::firstOrCreate(['email' => $email], [
                'name' => 'Local',
                'email_verified_at' => now(),
            ]);

            $user->email_verified_at ??= now();
            $user->save();

            if (! $user->ownedTeams()->exists()) {
                $this->createNewTeam->exec([
                    'name' => 'Local Workspace',
                    'user_id' => $user->id,
                ]);
            }

            return ['user' => $user, 'created' => $user->wasRecentlyCreated];
        });
    }
}
