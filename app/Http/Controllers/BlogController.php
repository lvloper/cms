<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Route;
use App\Models\Blog;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    public function show(Request $request, Route $route, Blog $blog)
    {
        if (!$route->image && $blog->image) {
            $route->image = $blog->image;
            $route->save();
        }

        if (config('cms.frontend') === 'react') {
            return Inertia::render('Cms/BlogPost', [
                'post' => [
                    'id' => $blog->id,
                    'title' => $blog->name,
                    'description' => $blog->description,
                    'content' => $blog->content,
                    'image' => $blog->image ? Storage::url($blog->image) : null,
                    'created_at' => $blog->published_at?->toISOString() ?? $blog->created_at?->toISOString(),
                ],
                'route' => [
                    'id' => $route->id,
                    'title' => $route->title,
                    'slug' => $route->slug,
                    'full_slug' => $route->full_slug,
                    'layout' => $route->layout ?? 'default',
                    'description' => $route->description,
                    'custom_css' => $route->custom_css,
                    'header_scripts' => $route->header_scripts,
                    'footer_scripts' => $route->footer_scripts,
                ],
                'layout' => $route->layout ?? 'default',
            ]);
        }

        return view('blog/post', [
            'blog' => $blog,
            'route' => $route,
        ]);
    }
}
