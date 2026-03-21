<?php

namespace App\Actions\Team;

use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * @phpstan-type DTOTeam array{
 *     name: string,
 *     user_id: int,
 * }
 */
class CreateNewTeam
{
    /**
     * Create a new team.
     *
     * @param DTOTeam $data
     * @return Team
     */
    public function exec(array $data): Team
    {
        $this->validate($data);

        /** @var Team $team */
        $team = Team::create([
            'name' => data_get($data, 'name'),
            'user_id' => data_get($data, 'user_id'),
        ]);

        return $team;
    }

    /**
     * Validate the team data.
     *
     * @param DTOTeam $data
     * @return void
     */
    private function validate(array $data): void
    {
        Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
        ])->validate();
    }
}
