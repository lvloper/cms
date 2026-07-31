<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PacoPageController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Models\Blog;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/hablemos', PacoPageController::class)->name('paco.show');

Route::get('/search-block', function (Request $request) {
    $query = $request->get('q', $request->get('s', ''));

    if (! $query || strlen($query) < 3) {
        return response()->json(['results' => []]);
    }

    $pages = Page::where('blocks', 'like', '%'.$query.'%')
        ->whereHas('route', function ($q) {
            $q->where('status', 'published');
        })
        ->with('route')
        ->limit(20)
        ->get();

    $results = $pages->map(function ($page) {
        return [
            'title' => $page->route->title ?? $page->name ?? 'Sin titulo',
            'url' => url($page->route->getFullPath()),
            'type' => 'page',
        ];
    });

    $blogs = Blog::where(function ($q) use ($query) {
        $q->where('description', 'like', '%'.$query.'%')
            ->orWhere('content', 'like', '%'.$query.'%');
    })
        ->whereHas('route', function ($q) {
            $q->where('status', 'published');
        })
        ->where('published_at', '<=', now())
        ->with('route')
        ->limit(20)
        ->get();

    $blogResults = $blogs->map(function ($blog) {
        return [
            'title' => $blog->route->title ?? $blog->name ?? 'Sin titulo',
            'url' => url($blog->route->getFullPath()),
            'type' => 'blog',
        ];
    });

    return response()->json([
        'results' => $results->concat($blogResults)->values()->toArray(),
    ]);
});

Route::get('/preview-blocks', function () {
    return view('components.blockLayout', ['slot' => '', 'hideFooter' => true, 'hideHeader' => true]);
})
    ->name('preview.blocks');

Route::get('/preview-blocks-minimal', function () {
    return view('components.blockLayout-minimal', ['slot' => '']);
})
    ->name('preview.blocks.minimal');

Route::get('/home', function () {
    return redirect('/');
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/search', [SearchController::class, 'index'])->name('search.index');

Route::get('/{slug?}', [RouteController::class, 'show'])
    ->where('slug', '.*')
    ->name('route.show');
