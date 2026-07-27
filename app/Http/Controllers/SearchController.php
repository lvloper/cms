<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $searchTerm = $request->get('s', session('searchTerm', ''));

        if (config('cms.frontend') === 'react') {
            return Inertia::render('Cms/Search', [
                'query' => $searchTerm,
                'results' => [],
                'route' => [
                    'title' => 'Buscar',
                    'slug' => 'search',
                    'layout' => 'default',
                ],
                'layout' => 'default',
            ]);
        }

        View::share('notPreview', true);
        View::share('index', false);
        View::share('isModal', false);
        View::share('layout', 'default');

        return view('pages.search-results', [
            'searchTerm' => $searchTerm,
        ]);
    }
}
