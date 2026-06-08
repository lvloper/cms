# Subagente: block-backend

Puede operar en dos modos: **creacion** (desde draft) o **update** (modificar campos de un bloque existente).

## Modo creacion

### Objetivo

Tomar un draft de bloque aprobado (`docs/cms/blocks/draft-<kebab>.md`) y generar:
1. La clase PHP del bloque (`app/Filament/Blocks/{Name}Block.php`)
2. La vista Blade inicial con dump de datos (`resources/views/blocks/{Name}.blade.php`)
3. El registro en el template correspondiente (`app/Filament/Templates/DefaultTemplate.php` o `ModalTemplate.php`)
4. El documento de props (`docs/cms/blocks/doc-{Name}.md`)

### Entradas requeridas

- `docs/cms/blocks/draft-<kebab>.md` — schema del bloque aprobado
- Convenciones del CMS (`PageBlock`, `Field::*`, route picker, image component)
- Template destino (`DefaultTemplate.php` por defecto)

### Restricciones

- No agregar campos que no esten en el draft.
- `NAME` debe coincidir con el nombre final del draft (PascalCase).
- `CATEGORY` usar existente o la definida en el draft.
- `LABEL` en espanol, breve y descriptivo.
- Usar componentes existentes (`Field::text`, `Field::rich`, `Field::route`, `Image`, etc.).
- Para Filament v5 usar `Filament\Schemas\Components\Grid`, `Section`, `Tabs`, `Group`.
- La vista inicial solo debe contener `@dump(get_defined_vars())`.
- La vista debe heredar de `<x-block>`.

### Proceso

1. Leer el draft y validar que tenga datos minimos (nombre, categoria, label, campos).
2. Mapear cada campo del draft a su equivalente Filament.
3. Definir required/optional/default/validaciones/helper text.
4. Generar clase PHP en `app/Filament/Blocks/{Name}Block.php`.
5. Generar vista Blade inicial en `resources/views/blocks/{Name}.blade.php`.
6. Registrar en `app/Filament/Templates/{Template}.php`.
7. Generar `docs/cms/blocks/doc-{Name}.md` con props documentados.
8. Ejecutar `php -l` sobre la clase generada.
9. Ejecutar `php artisan view:clear`.

### Salida obligatoria (creacion)

#### 1) Clase PHP

```php
<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\Field;

class {{Name}}Block extends PageBlock
{
    protected const NAME = '{{Name}}';

    protected const CATEGORY = '{{Category}}';

    protected const LABEL = '{{label en espanol}}';

    protected static function fields(): array
    {
        return [
            // campos mapeados del draft
        ];
    }
}
```

#### 2) Vista Blade inicial

```blade
<x-block>
    @dump(get_defined_vars())
</x-block>
```

#### 3) Documento de props (`doc-{Name}.md`)

```markdown
# {{Name}} — Props del bloque

## Schema

| Prop | Tipo | Requerido | Default | Descripcion |
|------|------|-----------|---------|-------------|
| `{{campo}}` | `{{tipo}}` | si/no | `{{default}}` | {{descripcion}} |

## Contrato de datos

```json
{
  "type": "{{Name}}",
  "data": {
    "{{campo}}": "{{ejemplo}}"
  }
}
```

## Reglas de renderizado

- {{reglas si aplican}}
```

#### 4) Checklist

- [ ] Clase PHP generada sin errores de sintaxis (`php -l`)
- [ ] Vista Blade creada con dump
- [ ] Bloque registrado en template
- [ ] `doc-{Name}.md` generado con props completos
- [ ] `php artisan view:clear` ejecutado

## Modo update

### Objetivo

Modificar campos de un bloque existente sin recrearlo desde cero.

### Entradas requeridas

- `docs/cms/blocks/doc-{Name}.md` — props actuales del bloque
- Clase PHP actual del bloque (`app/Filament/Blocks/{Name}Block.php`)
- Descripcion de los cambios: campos a agregar, modificar o eliminar

### Proceso

1. Leer la clase PHP actual y `doc-{Name}.md`.
2. Aplicar los cambios pedidos al array `fields()`.
3. Actualizar `doc-{Name}.md` con los props modificados.
4. Ejecutar `php -l` sobre la clase modificada.
5. Ejecutar `php artisan view:clear`.

### Salida obligatoria (update)

- Clase PHP actualizada
- `doc-{Name}.md` actualizado
- Checklist: cambios aplicados, `php -l` ok, `view:clear` ejecutado

## Criterios de done (ambos modos)

- El bloque es funcional desde el CMS (aparece en el block picker).
- La vista muestra toda la data cargada via `@dump`.
- El `doc-{Name}.md` documenta fielmente todos los props disponibles.
- No hay errores de sintaxis ni de registro.
