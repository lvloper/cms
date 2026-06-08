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

    public function isProtected(): bool
    {
        if (! $this->route) {
            return false;
        }

        $homeConfig = Configuration::getValue('home_route_id');
        $homeRouteId = $homeConfig['route']['route_id'] ?? null;

        $errorConfig = Configuration::getValue('error_404_route_id');
        $errorRouteId = $errorConfig['route']['route_id'] ?? null;

        return ((int) $this->route->id === (int) $homeRouteId) ||
               ((int) $this->route->id === (int) $errorRouteId);
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($page) {
            if ($page->isProtected()) {
                return false;
            }
            $page->route()->delete();
        });
    }
}
