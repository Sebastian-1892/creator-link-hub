<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = Profile::query()
            ->where('is_published', true)
            ->pluck('slug')
            ->map(fn (string $slug) => url('/p/'.$slug));

        $xml = view('seo.sitemap-xml', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
