<?php

namespace App\Actions\Team;

use App\Models\Team\Team;
use Illuminate\Support\Facades\Validator;

class RenameTeam
{
    public function exec(Team $team, string $name): Team
    {
        Validator::make(['name' => $name], [
            'name' => ['required', 'string', 'max:255'],
        ])->validate();

        $team->update(['name' => $name]);

        return $team;
    }
}
