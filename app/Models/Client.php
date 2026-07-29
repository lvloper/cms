<?php

namespace App\Models;

use App\Models\Traits\HasRoute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory, HasRoute;

    public const WORK_CATEGORIES = [
        'marketing' => 'Marketing',
        'design' => 'Diseño',
        'programming' => 'Programación',
        'branding' => 'Branding',
        'strategy' => 'Estrategia',
        'other' => 'Otros',
    ];

    public static bool $editLayout = true;

    public static string $routePrefix = 'cliente';

    protected $fillable = [
        'logo',
        'color',
        'popup_text_color',
        'is_featured',
        'blocks',
        'works',
        'testimonials',
        'preview_items',
    ];

    protected $casts = [
        'logo' => 'string',
        'color' => 'string',
        'popup_text_color' => 'string',
        'is_featured' => 'boolean',
        'blocks' => 'collection',
        'works' => 'collection',
        'testimonials' => 'collection',
        'preview_items' => 'collection',
    ];
}
