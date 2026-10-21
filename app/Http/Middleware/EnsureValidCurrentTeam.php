<?php

namespace App\Http\Middleware;

use App\Models\Team\Team;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidCurrentTeam
{
    /**
     * If the session "current_team" is no longer valid (e.g. user was removed),
     * switch to another workspace they can access and refresh the dashboard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        /** @var User $user */
        $user = Auth::user();
        $sessionTeam = session('current_team');

        if (! $sessionTeam instanceof Team) {
            return $next($request);
        }

        $team = Team::find($sessionTeam->getKey());

        if ($team && $user->belongsToTeam($team)) {
            return $next($request);
        }

        $replacement = $user->allTeams()->first();
        session(['current_team' => $replacement]);

        if (! $this->shouldRedirectAfterSwitch($request)) {
            return $next($request);
        }

        return redirect()
            ->route('dashboard')
            ->with('message', __('Your active workspace was switched because you no longer have access to the previous team.'))
            ->with('type', 'default');
    }

    private function shouldRedirectAfterSwitch(Request $request): bool
    {
        if ($request->isMethod('GET') && ! str_contains($request->path(), 'livewire')) {
            return true;
        }

        // Livewire in-flight request: full redirect so components reload with the new team.
        if ($request->isMethod('POST') && str_contains($request->path(), 'livewire/update')) {
            return true;
        }

        return false;
    }
}
