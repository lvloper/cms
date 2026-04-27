<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\View;

class SearchController extends Controller
{
    /**
     * Muestra la página de resultados de búsqueda.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Obtener el término de búsqueda de la solicitud o de la sesión
        $searchTerm = $request->get('s', session('searchTerm', ''));

        
        View::share('notPreview', true);
        View::share('index', false);
        View::share('isModal', false );
        View::share('layout',  'default' );
        
        return view('pages.search-results', [
            'searchTerm' => $searchTerm
        ]);
    }
}
