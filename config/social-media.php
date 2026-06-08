<?php

return [
    'networks' => [
        'instagram' => [
            'url' => env('SOCIAL_INSTAGRAM', 'https://www.instagram.com/'),
            'icon' => 'fab-instagram',
        ],
        'facebook' => [
            'url' => env('SOCIAL_FACEBOOK', 'https://www.facebook.com/'),
            'icon' => 'fab-facebook-f',
        ],
        'twitter' => [
            'url' => env('SOCIAL_TWITTER', 'https://x.com/'),
            'icon' => 'fab-x-twitter',
        ],
        'linkedin' => [
            'url' => env('SOCIAL_LINKEDIN', 'https://www.linkedin.com/'),
            'icon' => 'fab-linkedin-in',
        ],
        'youtube' => [
            'url' => env('SOCIAL_YOUTUBE', 'https://www.youtube.com/'),
            'icon' => 'fab-youtube',
        ],
        'tiktok' => [
            'url' => env('SOCIAL_TIKTOK', 'https://www.tiktok.com/'),
            'icon' => 'fab-tiktok',
        ],
    ]
]; 