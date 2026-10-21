<?php

namespace App\Http\Controllers\Pages;

use App\Actions\Diagram\ExportDiagramToJson;
use App\Actions\Diagram\ExportDiagramToLaravelMigration;
use App\Actions\Diagram\GenerateMigrationZip;
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

        cache()->forget('diagram:slug:'.$diagram->slug);
        cache()->forget('diagram');

        $diagram->load([
            'tables.columns',
            'tables.foreignKeys',
            'team',
        ]);

        return view('pages.diagram.show', compact('diagram'));
    }

    public function exportJson(int $teamId, Diagram $diagram): StreamedResponse
    {
        Gate::authorize('view', $diagram);

        $data = app(ExportDiagramToJson::class)->exec($diagram);
        $filename = str()->slug($diagram->name).'-'.now()->format('Ymd').'.json';

        return response()->streamDownload(
            fn () => print (json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
            $filename,
            ['Content-Type' => 'application/json']
        );
    }

    public function exportLaravelMigration(int $teamId, Diagram $diagram): StreamedResponse
    {
        Gate::authorize('view', $diagram);

        $files = app(ExportDiagramToLaravelMigration::class)->exec($diagram);
        $zipPath = app(GenerateMigrationZip::class)->exec($files);
        $filename = str()->slug($diagram->name).'-migrations-'.now()->format('Ymd').'.zip';

        return response()->streamDownload(
            function () use ($zipPath) {
                readfile($zipPath);
                @unlink($zipPath);
            },
            $filename,
            ['Content-Type' => 'application/zip']
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

        $diagram->is_favorite = ! $diagram->isFavorite();
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
