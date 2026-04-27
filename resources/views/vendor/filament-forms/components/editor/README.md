# Editor Components - Refactorización

Este directorio contiene los componentes modulares que conforman el editor de bloques de Filament.

## Estructura de Componentes

### Archivo Principal
- **`editor.blade.php`** (204 líneas) - Archivo principal que orquesta todos los componentes

### Componentes Modulares

#### 1. **device-selector.blade.php** (20 líneas)
Selector de dispositivos (móvil, tablet, escritorio) para vista previa responsive.

#### 2. **paste-handler-alpine.blade.php** (182 líneas)
Atributos Alpine.js para manejar la funcionalidad de pegar bloques desde el portapapeles.
- Detecta eventos de paste
- Valida estructura JSON
- Integra con Filament para crear bloques

#### 3. **block-header.blade.php** (127 líneas)
Cabecera de cada bloque que incluye:
- Icono del bloque
- Etiqueta/nombre del bloque
- Botones de reordenar
- Acciones del bloque

#### 4. **block-actions.blade.php** (62 líneas)
Botones de acción para cada bloque:
- Editar
- Clonar
- Copiar
- Eliminar
- Colapsar/Expandir

#### 5. **copy-block-button.blade.php** (106 líneas)
Botón especializado para copiar bloques al portapapeles con manejo de errores.

#### 6. **block-preview.blade.php** (36 líneas)
Contenedor de vista previa del bloque que decide entre mostrar preview o formulario.

#### 7. **iframe-preview.blade.php** (80 líneas)
Manejo del iframe de preview con:
- Carga lazy
- Sincronización de contenido
- Ajuste automático de altura
- Inicialización de Swiper/Alpine

#### 8. **block-render-content.blade.php** (53 líneas)
Renderizado del contenido del bloque dentro del iframe:
- Aplicación de estilos
- Manejo de clases
- Inyección de contenido

#### 9. **paste-button.blade.php** (122 líneas)
Botón de pegar con validación y manejo de clipboard API.

## Ventajas de la Refactorización

### Antes
- **868 líneas** en un solo archivo
- Difícil de mantener
- Código repetitivo
- Difícil de testear

### Después
- **204 líneas** en archivo principal (reducción del 76%)
- **9 componentes** modulares y reutilizables
- Cada componente con responsabilidad única
- Fácil de mantener y extender
- Mejor organización del código

## Uso

Los componentes se incluyen usando `@include` de Laravel Blade:

```blade
@include('filament-forms::components.editor.device-selector')

@include('filament-forms::components.editor.block-header', [
    'item' => $item,
    'uuid' => $uuid,
    // ... más parámetros
])
```

Para Alpine.js attributes:

```blade
<div @include('filament-forms::components.editor.paste-handler-alpine', ['statePath' => $statePath])>
    <!-- contenido -->
</div>
```

## Mantenimiento

Para modificar funcionalidad específica, ahora solo necesitas editar el componente correspondiente:

- **Cambiar estilo de device selector** → `device-selector.blade.php`
- **Modificar lógica de paste** → `paste-handler-alpine.blade.php`
- **Ajustar botones de acción** → `block-actions.blade.php`
- **Cambiar preview** → `iframe-preview.blade.php`

## Compatibilidad

Esta refactorización mantiene **100% de compatibilidad** con el código original. No se han modificado funcionalidades, solo se ha reorganizado el código en componentes más pequeños y manejables.

## Testing

Para verificar que todo funciona correctamente:

```bash
php artisan view:clear
php artisan view:cache
```

Si no hay errores, todos los componentes están correctamente configurados.
