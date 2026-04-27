<?php

namespace App\Models;

use App\Models\Traits\HasRoute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory, HasRoute;

    public static bool $editLayout = true;

    protected $fillable = ['name', 'blocks'];

    protected $casts = [
        'blocks' => 'collection',
    ];

    public function route()
    {
        return $this->morphOne(Route::class, 'routable');
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($post) {
            $post->route()->delete();
        });
    }
}
