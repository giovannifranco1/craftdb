<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Diagram;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $diagrams = Diagram::paginate();

        return view('dashboard', compact('diagrams'));
    }
}
