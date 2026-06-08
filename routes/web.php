<?php

use App\Http\Controllers\RouteController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/search-block', function () {
    $block = request('block');

    $pages = \App\Models\Page::where('blocks', 'like', "%{$block}%")->with('route')->get();

    foreach ($pages as $page) {
        if ($page->route) {
            echo "<a href='" . $page->route->getFullPath() . "'>" . $page->route->title . "</a><br>";
        }
    }
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
