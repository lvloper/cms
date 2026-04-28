---
name: create-filament-block
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
- Definir `protected const NAME`, `protected const LABEL` y `protected static function fields(): array`.
- `NAME` debe coincidir con la vista: `NAME = 'BaseCards'` usa `resources/views/blocks/BaseCards.blade.php`.
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

- Crear clase y vista.
- Registrar el bloque en el template correcto.
- Ejecutar `php -l` sobre la clase.
- Ejecutar `php artisan view:clear`.
- Probar `/admin/pages/create` y editar una página existente.
