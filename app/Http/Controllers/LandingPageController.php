<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class LandingPageController extends Controller
{
    public function index(): View
    {
        return view('landing-page.index', [
            'features' => config('landingpage.features'),
            'howItWorks' => config('landingpage.how_it_works'),
            'site' => config('landingpage.site'),
        ]);
    }

    public function features(): View
    {
        return view('landing-page.features', [
            'features' => config('landingpage.features'),
            'site' => config('landingpage.site'),
        ]);
    }

    public function pricing(): View
    {
        return view('landing-page.pricing', [
            'plans' => config('landingpage.pricing.plans'),
            'faq' => config('landingpage.faq'),
            'site' => config('landingpage.site'),
        ]);
    }

    public function about(): View
    {
        return view('landing-page.about', [
            'site' => config('landingpage.site'),
        ]);
    }
}

