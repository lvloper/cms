<?php

return [
    'custom_controllers' => [
        'App\Models\Blog' => 'App\Http\Controllers\BlogController',
        'App\Models\Client' => 'App\Http\Controllers\ClientController',
    ],
    'blog_index' => 'blog',
    'news_parent_id' => null,
];
