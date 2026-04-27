<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use App\Models\Status;

class RouteController extends Controller
{
    public function show(Request $request, $slug = 'home')
    {
        $query = Route::whereFullSlug($slug);

        $route = $query->firstOrFail();
        $routable = $route->routable;

        if ($route->notPublishedOrPreview()) {
            abort(404);
        }

        View::share('route', $route);
        View::share('notPreview', true);
        View::share('index', $route->layout == 'hasIndex' ? $route->getIndex() : false);
        View::share('isModal', false);
        View::share('layout', $route->layout ?? 'default');
        if ($route->layout == 'modal') {
            $parent = $route->parent ?? Page::where('id', 7)->first();

            $parentView = view('pages/blocksList', ['blocks' => $parent->routable->blocks, 'notLayout' => true])->render();

            View::share('isModal', true);
            View::share('parent', $parent);
            // Derive a safe parent URL to return to when closing the modal
            $parentUrl = method_exists($parent, 'getUrlAttribute') || isset($parent->url)
                ? ($parent->url ?? url('/'))
                : (method_exists($parent, 'getFullSlugAttribute') || isset($parent->full_slug)
                    ? url($parent->full_slug)
                    : url('/'));
            View::share('parentUrl', $parentUrl);
            View::share('parentView', $parentView);
        }


        // View::share('modal', $route->layout == 'hasModal' ? $route->getModal($routable->blocks) : false);

        $customControllers = Config::get('cms-routes.custom_controllers', []);
        $routeableClass = get_class($routable);

        if (array_key_exists($routeableClass, $customControllers)) {
            $controllerClass = $customControllers[$routeableClass];
            $controller = app()->make($controllerClass);
            return $controller->show($request, $route, $routable);
        }

        return view('pages/blocksList', ['blocks' => $routable->blocks]);
    }
}
