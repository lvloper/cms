# Button Block

## Descripción
Bloque genérico de botón configurable para usar en el editor TipTap.

## Archivos creados
- `/app/Filament/Blocks/ButtonBlock.php` - Definición del bloque
- `/resources/views/blocks/Button.blade.php` - Vista del bloque

## Características

### Estilos disponibles
- **Link** - Texto con color primary y subrayado al hacer hover
- **Rojo (Primary)** - Botón rojo con hover más oscuro
- **Violeta (Secondary)** - Botón violeta con hover más claro

### Tamaños disponibles
- **Pequeño (sm)** - padding 4/2, texto sm
- **Mediano (md)** - padding 6/3, texto base (por defecto)
- **Grande (lg)** - padding 8/4, texto lg

### Alineación disponible
- **Izquierda** - Alinea el botón a la izquierda
- **Centro** - Alinea el botón al centro (por defecto)
- **Derecha** - Alinea el botón a la derecha

## Configuración
El bloque utiliza el componente `FormShortcuts::RoutePicker` con:
- `btnLabel: true` - Permite personalizar el texto del botón
- `required: true` - El campo es obligatorio

## Uso
1. Agregar el bloque desde el editor TipTap
2. Seleccionar o crear una ruta con el Route Picker
3. Personalizar el texto del botón
4. Elegir estilo, tamaño y alineación
5. Guardar

## Clases CSS utilizadas
- Colores: `bg-primary`, `bg-secondary`, `text-primary`
- Hover: `bg-primary-hover`, `bg-secondary-hover`
- Transiciones: `transition-colors`

## Componentes relacionados
- `x-link` - Componente para enlaces con wire:navigate
- `x-block` - Wrapper para bloques del editor
