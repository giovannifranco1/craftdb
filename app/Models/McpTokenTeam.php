<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Stores the teams that an OAuth MCP token is allowed to access.
 * Created during the OAuth authorization flow when the user selects specific workspaces.
 * Tokens without entries here are unrestricted (e.g. stdio personal tokens).
 *
 * @property int $id
 * @property string $token_id
 * @property int $team_id
 */
class McpTokenTeam extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'team_id' => 'integer',
        ];
    }

    public static function allowedTeamIdsForToken(string $tokenId): ?array
    {
        $ids = static::where('token_id', $tokenId)->pluck('team_id')->all();

        return empty($ids) ? null : array_map('intval', $ids);
    }

    public static function storeForToken(string $tokenId, array $teamIds): void
    {
        foreach ($teamIds as $teamId) {
            static::firstOrCreate([
                'token_id' => $tokenId,
                'team_id'  => (int) $teamId,
            ]);
        }
    }
}
