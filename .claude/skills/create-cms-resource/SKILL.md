---
name: create-cms-resource
description: Crear un recurso CMS ruteable usando ResourceBase, CreateBase, EditBase y ListBase, similar a Page/Blog.
---

# Crear Recurso CMS Ruteable

Usar esta skill cuando se pida crear un recurso tipo `Portfolio`, `Project`, `Service`, etc. que tenga URL propia y use la configuración común de página.

## Archivos esperados

- Modelo: `app/Models/{Model}.php`
- Migración: `database/migrations/*_create_{table}.php`
- Resource: `app/Filament/Resources/{Model}Resource.php`
- Pages:
  - `app/Filament/Resources/{Model}Resource/Pages/List{Models}.php`
  - `app/Filament/Resources/{Model}Resource/Pages/Create{Model}.php`
  - `app/Filament/Resources/{Model}Resource/Pages/Edit{Model}.php`
- Opcional controller custom si no renderiza por `pages/blocksList`: `app/Http/Controllers/{Model}Controller.php` y `config/cms-routes.php`.

## Modelo

Debe usar `App\Models\Traits\HasRoute` y definir casts/fillables.

```php
<?php

namespace App\Models;

use App\Models\Traits\HasRoute;
use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    use HasRoute;

    public static bool $editLayout = true;

    protected $fillable = ['name', 'blocks', 'image'];

    protected $casts = [
        'blocks' => 'collection',
        'image' => 'string',
    ];
}
```

## Resource

Extender `ResourceBase` para heredar tabs de contenido + ruta/SEO.

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Bases\ResourceBase;
use App\Filament\Resources\PortfolioResource\Pages;
use App\Models\Portfolio;

class PortfolioResource extends ResourceBase
{
    protected static ?string $model = Portfolio::class;
    protected static ?string $modelLabel = 'Portfolio';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPortfolios::route('/'),
            'create' => Pages\CreatePortfolio::route('/create'),
            'edit' => Pages\EditPortfolio::route('/{record}/edit'),
        ];
    }
}
```

## Pages

```php
class ListPortfolios extends \App\Filament\Resources\Bases\ListBase
{
    protected static string $resource = \App\Filament\Resources\PortfolioResource::class;
}

class CreatePortfolio extends \App\Filament\Resources\Bases\CreateBase
{
    protected static string $resource = \App\Filament\Resources\PortfolioResource::class;
}

class EditPortfolio extends \App\Filament\Resources\Bases\EditBase
{
    protected static string $resource = \App\Filament\Resources\PortfolioResource::class;
}
```

## Migración mínima

```php
Schema::create('portfolios', function (Blueprint $table) {
    $table->id();
    $table->string('name')->nullable();
    $table->json('blocks')->nullable();
    $table->string('image')->nullable();
    $table->timestamps();
});
```

## Checklist

- Crear migración, modelo, resource y pages.
- Verificar que el modelo tenga relación `route()` vía `HasRoute`.
- Si usa bloques, `blocks` debe ser `json` y cast `collection`.
- Ejecutar `php artisan migrate`.
- Ejecutar `php artisan route:list --path=admin/{resource-slug}`.
- Probar index/create/edit en admin con crawl autenticado.
