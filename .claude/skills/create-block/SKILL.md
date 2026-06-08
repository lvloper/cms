---
name: create-block
description: Crear un bloque del page builder CMS basado en PageBlock, con fields Filament v5, vista Blade y registro en templates.
---

# Crear Bloque Filament CMS

Usar esta skill cuando se pida crear un bloque nuevo para el builder de páginas.

## Estructura

- Clase: `app/Filament/Blocks/{Name}Block.php`
- Vista frontend: `resources/views/blocks/{Name}.blade.php`
- Registro: `app/Filament/Templates/DefaultTemplate.php` o `ModalTemplate.php`
- Tipo guardado en DB: `{Name}`

## Reglas

- La clase debe extender `App\Filament\Blocks\PageBlock`.
- Definir `protected const NAME`, `protected const CATEGORY`, `protected const LABEL` y `protected static function fields(): array`.
- `CATEGORY` agrupa bloques en el block picker. Categorias existentes: `Hero`, `Contenido`, `Multimedia`, `Listados`, `Interaccion`, `Datos`, `Area`. Podes usar una nueva.
- `NAME` debe coincidir con la vista: `NAME = 'BaseCards'` usa `resources/views/blocks/BaseCards.blade.php`.
- Cuando uses `Image::make()` o `Field::image()`, siempre especifica width y height. Si queres forzar una relacion de aspecto especifica (no solo redimensionar), pasa `forceRatio: true`. El calculo basado en GCD fuerza la proporcion exacta en el editor de imagenes.
- Para Filament v5 usar:
  - `Filament\Schemas\Components\Grid`, `Section`, `Tabs`, `Group` para layout.
  - `Filament\Schemas\Components\Utilities\Get/Set` para closures.
  - `Filament\Actions\Action` para acciones.
- Usar componentes custom en `app/Filament/Forms/Components` para rutas, imágenes, galerías e iconos.
- Si usás RichEditor de Filament, guardar y renderizar HTML directamente.

## Ejemplo mínimo

```php
<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\Field;

class BaseExampleBlock extends PageBlock
{
    protected const NAME = 'BaseExample';

    protected const CATEGORY = 'Contenido';

    protected const LABEL = 'Base: ejemplo';

    protected static function fields(): array
    {
        return [
            Field::text('title', 'Título')->required(),
            Field::textarea('description', 'Descripción')->rows(3),
        ];
    }
}
```

```blade
<x-block class="py-12 md:py-20">
    <div class="container mx-auto">
        <h2 class="text-3xl font-bold">{{ $title ?? '' }}</h2>
        @if(!empty($description))
            <p class="mt-4 text-lg text-gray-700">{{ $description }}</p>
        @endif
    </div>
</x-block>
```

## Checklist

- Crear clase con NAME, CATEGORY, LABEL constants y vista.
- Registrar el bloque en el template correcto.
- Ejecutar `php -l` sobre la clase.
- Ejecutar `php artisan view:clear`.
- Probar `/admin/pages/create` y editar una página existente.
