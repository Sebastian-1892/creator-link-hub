<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class MarketingController extends Controller
{
    public function home(): View
    {
        return view('marketing.home');
    }

    public function pricing(): View
    {
        return view('marketing.pricing');
    }

    public function faq(): View
    {
        return view('marketing.faq');
    }

    public function help(): View
    {
        return view('marketing.help');
    }
}
