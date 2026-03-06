<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFacade;

class LandingPageController extends Controller
{
    public function index(): View
    {
        return ViewFacade::make('pages.landing-page.index');
    }

    public function features(): View
    {
        return ViewFacade::make('pages.landing-page.features');
    }

    public function pricing(): View
    {
        return ViewFacade::make('pages.landing-page.pricing');
    }

    public function about(): View
    {
        return ViewFacade::make('pages.landing-page.about');
    }
}

