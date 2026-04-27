<?php

namespace App\Models;

use App\Models\Traits\HasRoute;
use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Page extends Model
{
    use HasFactory, HasRoute, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable();
    }


}
