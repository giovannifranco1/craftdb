<?php

namespace App\Actions\Team;

use App\Actions\Diagram\DeleteDiagram;
use App\Models\Team\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteTeam
{
    public function __construct(private DeleteDiagram $deleteDiagram) {}

    /**
     * Delete a workspace and all of its diagrams. The last workspace can't be deleted.
     */
    public function exec(Team $team): void
    {
        if (Team::query()->where('user_id', $team->user_id)->count() <= 1) {
            throw ValidationException::withMessages([
                'team' => __('You cannot delete your only workspace.'),
            ]);
        }

        DB::transaction(function () use ($team): void {
            $team->diagrams()->each(fn ($diagram) => $this->deleteDiagram->exec($diagram));
            $team->delete();
        });
    }
}
