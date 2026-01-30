<?php

namespace App\Http\Middleware;

use App\Models\Team\Team;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckTeamRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $roles  Pipe-separated list of allowed roles (e.g., 'viewer|editor|owner')
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = Auth::user();
        $teamId = $request->route('team_id');
        $team = Team::find($teamId);

        abort_if(!$user, 401, __('Unauthenticated.'));
        abort_if(!$teamId, 400, __('Team ID is required.'));
        abort_if(!$team, 404, __('Team not found.'));

        $allowedRoles = explode('|', $roles);

        if (in_array('owner', $allowedRoles) && $user->ownsTeam($team)) {
            return $next($request);
        }

        $teamMembership = $user->teams()
            ->where('team_id', $teamId)
            ->first();

        abort_if(!$teamMembership, 403, __('You do not have access to this team.'));
        abort_if(!$teamMembership->userHasRole($user, $allowedRoles), 403, __('You do not have permission to access this resource.'));

        return $next($request);
    }
}
