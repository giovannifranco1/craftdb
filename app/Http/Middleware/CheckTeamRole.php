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
        /** @var User $user */
        $user = Auth::user();

        $teamId = $request->route('team_id');
        $team = Team::find($teamId);

        abort_if(! $user, 401, __('Unauthenticated.'));
        abort_if(! $teamId, 400, __('Team ID is required.'));
        abort_if(! $team, 404, __('Team not found.'));

        $allowedRoles = explode('|', $roles);

        if (in_array('owner', $allowedRoles) && $user->ownsTeam($team)) {
            return $next($request);
        }

        $teamMembership = $user->teams()
            ->where('team_id', $teamId)
            ->first();

        if (! $teamMembership) {
            if ($user->allTeams()->isNotEmpty()) {
                session(['current_team' => $user->allTeams()->first()]);

                return redirect()
                    ->route('dashboard')
                    ->with('message', __('You do not have access to that team. Your workspace was switched.'))
                    ->with('type', 'default');
            }

            abort(403, __('You do not have access to this team.'));
        }
        abort_if(!$teamMembership->userHasRole($user, $allowedRoles), 403, __('You do not have permission to access this resource.'));

        return $next($request);
    }
}
