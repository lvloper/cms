<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Configuration extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type'
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Boot the model and set up event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when configuration is saved
        static::saved(function ($configuration) {
            Cache::forget("config.{$configuration->key}");
        });

        // Clear cache when configuration is updated
        static::updated(function ($configuration) {
            Cache::forget("config.{$configuration->key}");
            
            // Also clear cache for the original key if it was changed
            if ($configuration->isDirty('key')) {
                $originalKey = $configuration->getOriginal('key');
                if ($originalKey) {
                    Cache::forget("config.{$originalKey}");
                }
            }
        });

        // Clear cache when configuration is deleted
        static::deleted(function ($configuration) {
            Cache::forget("config.{$configuration->key}");
        });
    }

    /**
     * Get the available configuration types
     */
    public static function getTypes(): array
    {
        return [
            'text' => 'Texto',
            'rich_text' => 'Texto enriquecido',
            'url' => 'URL/Enlace',
            'image' => 'Imagen',
        ];
    }

    /**
     * Get configuration value by key
     */
    public static function getValue(string $key, $default = null)
    {
        $config = static::where('key', $key)->first();
        return $config ? $config->value : $default;
    }

    /**
     * Set configuration value
     */
    public static function setValue(string $key, $value, string $type = 'text'): Configuration
    {
        $configuration = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type
            ]
        );

        // Clear cache immediately
        Cache::forget("config.{$key}");

        return $configuration;
    }

    /**
     * Get the badge color for the type
     */
    public function getTypeBadgeColor(): string
    {
        return match($this->type) {
            'text' => 'gray',
            'rich_text' => 'gray',
            'url' => 'gray',
            'image' => 'gray',
            default => 'gray',
        };
    }
}
