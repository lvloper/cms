<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\BannerLocation;
use App\Enums\Status;

class Banner extends Model
{

    protected $fillable = ['title', 'image', 'location', 'route', 'status'];

    protected $casts = [
        'location' => BannerLocation::class,
        'route' => 'array',
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
}
