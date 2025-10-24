<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class TesteController extends Controller
{
    public function index(): View {
        return view('teste');
    }
}
