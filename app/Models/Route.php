<?php

namespace App\Models;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $layout
 * @property Status $status
 * @property string $full_slug
 * @property int $parent_id
 * @property string $description
 * @property string $image
 * @property string $url
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at


  * HasRoute
 * @function string getFullPath()*/

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Traits\HasPreview;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Illuminate\Support\Facades\Storage;



class Route extends Model
{
    use HasPreview, HasSEO;

    protected $fillable = ['title', 'slug', 'layout', 'status', 'full_slug', 'parent_id', 'description', 'image', 'custom_css', 'header_scripts', 'footer_scripts', 'routable_type', 'routable_id'];

    protected static ?int $forceParent;

    public function getDynamicSEOData(): SEOData
    {
        // Override only the properties you want:
        return new SEOData(
            title: $this->title,
            description: $this->description,
            image: $this->image ? Storage::url($this->image) : null,
            locale: 'es_AR',
        );
    }

    // public static function initForceParent()
    // {
    //     static::$forceParent = config(key: 'cms-routes.news_parent_id');
    // }

    protected $casts = [
        'status' => Status::class,
        'image' => 'string',
    ];

    /**
     * Ensure image is always a string, not an array
     */
    public function setImageAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['image'] = $value[0] ?? null;
        } else {
            $this->attributes['image'] = $value;
        }
    }


    protected static function boot()
    {
        parent::boot();

        static::saving(function ($route) {

            if ($route->parent_id && $route->parent_id === $route->id) {
                throw new \InvalidArgumentException('The parent and child routes cannot have the same ID.');
            }

            $route->full_slug = $route->full_slug ?? $route->getFullPath();
        });
    }

    public function routable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'routable_type', 'routable_id');
    }

    public function getIndex(): array
    {
        $blocks = $this->routable->blocks;
        return collect($blocks)
            ->filter(function ($block) {
                return isset($block['data']['blockTitle']) && !empty($block['data']['blockTitle']);
            })
            ->map(function ($block) {
                return [
                    'title' => $block['data']['blockTitle'],
                    'id' => \Illuminate\Support\Str::slug($block['data']['blockTitle']) ?? null,
                ];
            })
            ->values()
            ->toArray();
    }

    public function parent()
    {
        return $this->belongsTo(Route::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Route::class, 'parent_id');
    }

    public function getFullSlugAttribute()
    {
        $slugs = collect([]);
        $route = $this;

        while ($route) {
            $slugs->prepend($route->slug);
            $route = $route->parent;
        }

        return $slugs->implode('/');
    }

    public function getFullPath()
    {
        if ($this->parent_id) {
            return $this->parent->getFullPath() . '/' . $this->slug;
        }
        return $this->slug;
    }

    public function getUrlAttribute()
    {
        return url($this->full_slug);
    }

    /**
     * Obtiene todos los IDs de rutas descendientes de forma recursiva
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDescendantIds()
    {
        $ids = collect([$this->id]);

        $children = $this->children()->with('children')->get();

        $children->each(function($child) use (&$ids) {
            $ids = $ids->merge($child->getDescendantIds());
        });

        return $ids->unique();
    }

    public function scopeWhereFullSlug($query, $slug)
    {
        if ($slug === 'home') {
            return $query->where('slug', 'home');
        }

        return $query->where('full_slug', $slug);
    }

    public static function getSelectOptions($search = null, $addExternal = false, ?\Closure $filter = null): array
    {
        $routeOptions = \App\Models\Route::with('children')
            ->orderBy('title', 'asc')
            ->when($filter, function ($query) use ($filter) {
                $query->whereNotNull('parent_id');
            }, function ($query) {
                $query->whereNull('parent_id');
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhereHas('children', function ($q) use ($search) {
                            $q->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filter, function ($query) use ($filter) {
                $filter($query);
            })
            ->get()
            ->mapWithKeys(function ($route) use ($search) {
                $options = [(string)$route->id => $route->title];

                // Si hay búsqueda, mostramos todas las rutas hijas que coincidan
                // Si no hay búsqueda, limitamos a 5
                $children = $search ? $route->children : $route->children->take(5);

                foreach ($children as $child) {
                    // Si hay búsqueda, solo agregar si coincide con el término de búsqueda
                    if (!$search || stripos($child->title, $search) !== false) {
                        $options[(string)$child->id] = '-- ' . $child->title;
                    }
                }
                return $options;
            })
            ->toArray();

        if ($addExternal) {
            $routeOptions['0'] = 'Enlace externo';
        }

        return $routeOptions;
    }

    public function isActive(): bool
    {
        $homeConfig = \App\Models\Configuration::getValue('home_route_id');
        $homeRouteId = $homeConfig['route']['route_id'] ?? null;

        if ($homeRouteId && (int) $this->id === (int) $homeRouteId) {
            return request()->path() === '/';
        }

        return request()->path() === ($this->full_slug === 'home' ? '/' : $this->full_slug);
    }

    public function isActiveParent(): bool
    {
        // Si esta ruta es la activa, es un padre activo
        if ($this->isActive()) {
            return true;
        }

        // Verificar si alguno de los hijos en el menú está activo
        $menu = \App\Models\Menu::where('slug', 'header')->first();
        if ($menu) {
            foreach ($menu->items as $item) {
                // Si este item es la ruta activa y tiene hijos
                if ($item['route']['route_id'] == $this->id && isset($item['children'])) {
                    // Verificar si algún hijo está activo
                    foreach ($item['children'] as $child) {
                        $childRoute = static::find($child['route']['route_id']);
                        if ($childRoute && $childRoute->isActive()) {
                            return true;
                        }
                    }
                }
                // Si este es un hijo y su padre está activo
                elseif (isset($item['children'])) {
                    foreach ($item['children'] as $child) {
                        if (
                            $child['route']['route_id'] == $this->id &&
                            ($parentRoute = static::find($item['route']['route_id'])) &&
                            $parentRoute->isActive()
                        ) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    public function notPublishedOrPreview(): bool
    {
        if ($this->status == Status::Published) {
            return false;
        }

        if ($this->status == Status::Hidden) {
            return false;
        }

        return !$this->isPreview();
    }

    /**
     * Scope to get only routes that should be included in sitemap
     */
    public function scopeForSitemap($query)
    {
        return $query->where('status', Status::Published);
    }

    /**
     * Get the sitemap priority for this route
     */
    public function getSitemapPriority(): string
    {
        return $this->parent_id ? '0.7' : '0.9';
    }

    /**
     * Get the canonical URL for this route
     */
    public function getCanonicalUrl(): string
    {
        $homeConfig = \App\Models\Configuration::getValue('home_route_id');
        $homeRouteId = $homeConfig['route']['route_id'] ?? null;

        if ($homeRouteId && (int) $this->id === (int) $homeRouteId) {
            return url('/');
        }

        return $this->full_slug === 'home' ? url('/') : url($this->full_slug);
    }


}
