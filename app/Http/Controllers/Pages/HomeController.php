<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Diagram;
use App\Models\Team\Team;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {

        $diagramsCount = Diagram::query()
            ->where('team_id', Team::currentTeam()->getKey())
            ->count();

        return view('dashboard', [
            'diagramsCount' => $diagramsCount,
        ]);
    }
}
