<?php

namespace App\Models;

use App\Observers\RedirectionObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Redirection extends Model
{
    use HasFactory;

    protected $fillable = [
        'old_url',
        'new_url',
        'redirect_code',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'redirect_code' => 'integer',
    ];

    protected static function booted(): void
    {
        // Registrar el observer para limpiar cache
        static::observe(RedirectionObserver::class);
    }

    /**
     * Normaliza un path relativo: quita dominio, asegura prefijo '/', quita trailing slash (excepto raíz)
     */
    public static function normalizePath(?string $url): string
    {
        $url = (string) $url;
        $url = trim($url);
        if ($url === '') {
            return '/';
        }
        // Remover dominio base si está presente
        $base = config('app.url');
        if ($base && str_starts_with($url, $base)) {
            $url = substr($url, strlen($base));
        }
        // Asegurar prefijo '/'
        if (! str_starts_with($url, '/')) {
            $url = '/'.$url;
        }
        // Quitar trailing slash salvo raíz
        if ($url !== '/' && str_ends_with($url, '/')) {
            $url = rtrim($url, '/');
        }

        return $url;
    }

    /**
     * Mutator: guardar old_url normalizada
     */
    public function setOldUrlAttribute($value): void
    {
        $this->attributes['old_url'] = self::normalizePath($value);
    }

    /**
     * Mutator: mantener externas tal cual; relativas sin dominio y con '/'
     */
    public function setNewUrlAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['new_url'] = null;

            return;
        }
        $val = trim((string) $value);
        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) {
            $this->attributes['new_url'] = $val;

            return;
        }
        // Normalizar relativa sin dominio y con prefijo '/'
        $val = self::normalizePath($val);
        // Guardar sin dominio, pero con prefijo '/'
        $this->attributes['new_url'] = $val;
    }

    /**
     * Scope para redirecciones activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para buscar por URL de origen
     */
    public function scopeByOldUrl($query, $url)
    {
        return $query->where('old_url', $url);
    }

    /**
     * Obtener códigos de redirección disponibles
     */
    public static function getRedirectCodes(): array
    {
        return [
            301 => '301 - Movido permanentemente',
            302 => '302 - Movido temporalmente',
            303 => '303 - Ver otro',
            307 => '307 - Redirección temporal',
            308 => '308 - Redirección permanente',
        ];
    }

    /**
     * Formatear URL para ser relativa
     */
    public function getFormattedOldUrlAttribute(): string
    {
        $url = $this->old_url;

        // Remover el dominio base si está presente
        $baseUrl = config('app.url');
        if (str_starts_with($url, $baseUrl)) {
            $url = substr($url, strlen($baseUrl));
        }

        // Asegurar que empiece con /
        if (! str_starts_with($url, '/')) {
            $url = '/'.$url;
        }

        return $url;
    }

    /**
     * Verificar si la URL de destino es externa
     */
    public function getIsExternalAttribute(): bool
    {
        if (! $this->new_url) {
            return false;
        }

        return str_starts_with($this->new_url, 'http://') || str_starts_with($this->new_url, 'https://');
    }

    /**
     * Obtener URL de destino completa
     */
    public function getFullNewUrlAttribute(): string
    {
        if (! $this->new_url) {
            return '';
        }

        if ($this->is_external) {
            return $this->new_url;
        }

        $url = $this->new_url;

        // Asegurar que empiece con / para URLs relativas
        if (! str_starts_with($url, '/')) {
            $url = '/'.$url;
        }

        return url($url);
    }

    /**
     * Limpiar todo el cache de redirecciones
     */
    public static function clearAllCache(): void
    {
        // Obtener todas las URLs activas y limpiar su cache (normalizadas)
        static::where('is_active', true)->pluck('old_url')->each(function ($url) {
            $normalized = self::normalizePath($url);
            Cache::forget('redirection:'.md5($normalized));
            // Limpiar variantes comunes por si quedaron llaves viejas
            Cache::forget('redirection:'.md5(ltrim($normalized, '/')));
            Cache::forget('redirection:'.md5($normalized.'/'));
        });
    }
}
