<?php

namespace App\Models;

use App\Models\Traits\HasPublishedDate;
use App\Models\Traits\HasRoute;
use App\Models\Traits\HasThumb;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Tags\HasTags;
use Spatie\Tags\Tag;

class Blog extends Model
{
    use HasFactory, HasPublishedDate, HasRoute, HasTags, HasThumb;

    public function getDefaultRouteParentId(): ?int
    {
        return config('cms-routes.news_parent_id');
    }

    protected $table = 'blog';

    public static ?int $forceParent = null;

    public static bool $editDate = true;

    protected $fillable = ['name', 'description', 'content', 'image', 'published_at'];

    protected $casts = [
        'blocks' => 'collection',
        'image' => 'string',
        'description' => 'string',
        'content' => 'string',
    ];

    /**
     * Get description attribute and handle empty/null values
     */
    public function getDescriptionAttribute($value)
    {
        if (empty($value) || $value === null) {
            return null;
        }

        return $value;
    }

    /**
     * Get content attribute and handle empty/null values
     */
    public function getContentAttribute($value)
    {
        if (empty($value) || $value === null) {
            return null;
        }

        return $value;
    }

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

        static::$forceParent = config('cms-routes.news_parent_id');

        static::creating(function ($blog) {
            Cache::forget('highlight_tags');

        });

        static::deleting(function ($blog) {
            Cache::forget('highlight_tags');
        });

        static::updating(function ($blog) {
            Cache::forget('highlight_tags');
        });

    }

    public function isOldBlog()
    {
        if ($this->published_at > '2025-01-01') {
            return false;
        }

        return $this->getOldBlog() !== null;
    }

    public function getOldBlog()
    {
        return BlogOldData::where('new_id', $this->id)->first();
    }

    public function getShortDescriptionAttribute()
    {
        return Str::limit(strip_tags($this->description ?? ''), 250);
    }

    public function next(): ?Blog
    {
        return Blog::where('published_at', '>', $this->published_at)->orderBy('published_at', 'asc')->isPublished()->first();
    }

    public function previous(): ?Blog
    {
        return Blog::where('published_at', '<', $this->published_at)->orderBy('published_at', 'desc')->isPublished()->first();
    }

    public static function tagRoute(Tag $tag)
    {
        return url(config('cms-routes.blog_index').'?tag='.$tag->slug);
    }

    public function related(): Collection
    {
        $tags = $this->tags->pluck('id');

        $posts = Blog::whereHas('tags', function ($query) use ($tags) {
            $query->whereIn('tags.id', $tags);
        })
            ->where('id', '!=', $this->id)
            ->isPublished()
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        if ($posts->isEmpty() || $posts->count() < 3) {
            // Si no hay posts relacionados por tags o son menos de 3,
            // buscar posts del mismo año
            $year = $this->published_at->year;

            $posts = Blog::where('id', '!=', $this->id)
                ->whereYear('published_at', $year)
                ->isPublished()
                ->orderBy('published_at', 'desc')
                ->limit(3)
                ->get();

            // Si aún no hay suficientes posts, buscar en otros años
            if ($posts->count() < 3) {
                $remaining = 3 - $posts->count();
                $additionalPosts = Blog::where('id', '!=', $this->id)
                    ->isPublished()
                    ->orderByRaw('ABS(DATEDIFF(published_at, ?))', [$this->published_at])
                    ->limit($remaining)
                    ->get();

                $posts = $posts->concat($additionalPosts);
            }
        }

        return $posts;
    }
}
