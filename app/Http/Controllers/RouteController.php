<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
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
        try {
            if ($slug === 'home' || $slug === '') {
                $homeConfig = Configuration::getValue('home_route_id');
                $homeRouteId = $homeConfig['route']['route_id'] ?? null;
                $route = $homeRouteId ? Route::findOrFail($homeRouteId) : Route::where('slug', 'home')->firstOrFail();
            } else {
                $route = Route::whereFullSlug($slug)->firstOrFail();
            }

            $routable = $route->routable;

            if ($route->notPublishedOrPreview()) {
                return $this->errorPage();
            }
        } catch (\Exception $e) {
            return $this->errorPage();
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
            $parentUrl = method_exists($parent, 'getUrlAttribute') || isset($parent->url)
                ? ($parent->url ?? url('/'))
                : (method_exists($parent, 'getFullSlugAttribute') || isset($parent->full_slug)
                    ? url($parent->full_slug)
                    : url('/'));
            View::share('parentUrl', $parentUrl);
            View::share('parentView', $parentView);
        }

        $customControllers = Config::get('cms-routes.custom_controllers', []);
        $routeableClass = get_class($routable);

        if (array_key_exists($routeableClass, $customControllers)) {
            $controllerClass = $customControllers[$routeableClass];
            $controller = app()->make($controllerClass);
            return $controller->show($request, $route, $routable);
        }

        return view('pages/blocksList', ['blocks' => $routable->blocks]);
    }

    protected function errorPage()
    {
        $errorConfig = Configuration::getValue('error_404_route_id');
        $errorRouteId = $errorConfig['route']['route_id'] ?? null;

        if ($errorRouteId) {
            $route = Route::find($errorRouteId);
            if ($route && $route->routable) {
                View::share('route', $route);
                View::share('notPreview', true);
                View::share('isModal', false);
                View::share('layout', $route->layout ?? 'default');

                return response(view('pages/blocksList', ['blocks' => $route->routable->blocks]), 404);
            }
        }

        abort(404);
    }
}
