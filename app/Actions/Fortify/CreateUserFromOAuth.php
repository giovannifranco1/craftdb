<?php

namespace App\Actions\Fortify;

use App\Exceptions\EmailAlreadyRegisteredException;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class CreateUserFromOAuth
{
    /**
     * Create or update a user from OAuth provider.
     *
     * @param  string  $provider
     * @param  SocialiteUser  $socialiteUser
     * @return User
     */
    public function exec(string $provider, SocialiteUser $socialiteUser): User
    {
        return DB::transaction(function () use ($provider, $socialiteUser): User {

            if (!User::isProvider($provider)) {
                throw new \Exception("Provider {$provider} is not supported");
            }

            $providerId = $socialiteUser->getId();
            $email = $socialiteUser->getEmail();
            $name = $socialiteUser->getName() ?? $socialiteUser->getNickname();

            $user = User::where("{$provider}_id", $providerId)->first();

            if (!$user && $email) {
                $existingByEmail = User::where('email', $email)->whereNull("{$provider}_id")->first();
                if ($existingByEmail) {
                    throw EmailAlreadyRegisteredException::forProvider();
                }
            }

            $user = User::updateOrCreate(
                ["{$provider}_id" => $providerId],
                [
                    'name' => $name,
                    'email' => $email,
                    "{$provider}_token" => $socialiteUser->token,
                    "{$provider}_refresh_token" => $socialiteUser->refreshToken,
                ]
            );

            $this->createTeam($user);

            return $user;
        });
    }

    /**
     * Create a default team for the user.
     *
     * @param  User  $user
     * @return void
     */
    protected function createTeam(User $user): void
    {
        if ($user->ownedTeams()->count() === 0) {
            $user->ownedTeams()->save(Team::forceCreate([
                'user_id' => $user->id,
                'name' => explode(' ', $user->name, 2)[0] . "'s Team",
            ]));
        }
    }
}
