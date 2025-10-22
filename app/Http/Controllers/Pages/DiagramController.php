<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Diagram;
use Illuminate\Contracts\View\View;

class DiagramController extends Controller
{
    /** Show Diagram */
    public function show(Diagram $diagram): View
    {
        $tables = $diagram->tables()
            ->withForeignCount()
            ->withPrimaryCount()
            ->withCount('columns')
            ->get();

        return view('pages.diagram.show', compact('diagram', 'tables'));
    }
}
