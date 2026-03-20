<?php

namespace App\Http\Controllers\Pages;

use App\Actions\Diagram\ExportDiagramToJson;
use App\Http\Controllers\Controller;
use App\Models\Diagram;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DiagramController extends Controller
{
    /** Show Diagram */
    public function show(int $teamId, Diagram $diagram): View
    {
        Gate::authorize('view', $diagram);

        $user = Auth::user();

        if ($user) {
            $diagram->recordView($user);
        }

        cache()->forget('diagram:slug:' . $diagram->slug);
        cache()->forget('diagram');

        $diagram->load([
            'tables.columns',
            'tables.foreignKeys',
            'team.owner',
        ]);

        return view('pages.diagram.show', compact('diagram'));
    }

     /** Show Public Diagram (no authentication required) */
     public function showPublic(string $token): View
     {
         $diagram = Diagram::where('public_token', $token)->firstOrFail();

         $diagram->load([
             'tables.columns',
             'tables.foreignKeys',
             'areas',
             'notes',
         ]);

         return view('pages.diagram.public', compact('diagram'));
     }

    public function exportJson(int $teamId, Diagram $diagram): StreamedResponse
    {
        Gate::authorize('view', $diagram);

        $data = app(ExportDiagramToJson::class)->exec($diagram);
        $filename = str()->slug($diagram->name) . '-' . now()->format('Ymd') . '.json';

        return response()->streamDownload(
            fn () => print(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
            $filename,
            ['Content-Type' => 'application/json']
        );
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
