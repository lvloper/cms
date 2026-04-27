<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Route;
use App\Models\Blog;

class BlogController extends Controller
{
    public function show(Request $request, Route $route, Blog $blog)
    {
        if(!$route->image && $blog->image) {
            $route->image = $blog->image;
            $route->save();
        }

        return view('blog/post', [
            'blog' => $blog,
            'route' => $route,
        ]);
    }
}