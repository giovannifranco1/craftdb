<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Diagram;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DiagramController extends Controller
{
    /** Show Diagram */
    public function show(int $teamId, Diagram $diagram): View
    {
        Gate::authorize('view', $diagram);

        // Register that the user viewed the diagram
        $diagram->recordView(Auth::user());

        return view('pages.diagram.show', compact('diagram'));
    }
}
