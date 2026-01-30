<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFacade;

class LandingPageController extends Controller
{
    public function index(): View
    {
        return ViewFacade::make('landing-page.index', [
            'features' => config('landingpage.features'),
            'howItWorks' => config('landingpage.how_it_works'),
            'plans' => config('landingpage.pricing.plans'),
            'faq' => config('landingpage.faq'),
            'site' => config('landingpage.site'),
        ]);
    }

    public function features(): View
    {
        return ViewFacade::make('landing-page.features', [
            'features' => config('landingpage.features'),
            'site' => config('landingpage.site'),
        ]);
    }

    public function pricing(): View
    {
        return ViewFacade::make('landing-page.pricing', [
            'plans' => config('landingpage.pricing.plans'),
            'faq' => config('landingpage.faq'),
            'site' => config('landingpage.site'),
        ]);
    }

    public function about(): View
    {
        return ViewFacade::make('landing-page.about', [
            'site' => config('landingpage.site'),
        ]);
    }
}

