<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Diagram;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\JsonResponse;

class DiagramController extends Controller
{
    /** Show Diagram */
    public function show(int $teamId, Diagram $diagram): View
    {
        // Gate::authorize('view', $diagram);

        // Register that the user viewed the diagram
        $user = Auth::user();
        if ($user) {
            $diagram->recordView($user);
        }

        return view('pages.diagram.show', compact('diagram'));
    }

    public function toggleFavorite(int $teamId, Diagram $diagram): JsonResponse
    {
        if (Gate::denies('update', $diagram) || $diagram->getTeamId() !== $teamId) {
            return response()->json([
                'success' => false,
                'message' => __('You are not authorized to perform this action on this diagram.'),
            ], 403);
        }

        $diagram->is_favorite = !$diagram->isFavorite();
        $diagram->timestamps = false;
        $diagram->save();

        return response()->json([
            'success' => true,
            'message' => $diagram->isFavorite()
                ? __('Diagram :name was added to your favorites', ['name' => $diagram->name])
                : __('Diagram :name was removed from your favorites', ['name' => $diagram->name]
            ),
        ]);
    }
}
