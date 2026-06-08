# Block: Cards

## Meta
- **Nombre:** Cards
- **Categoría:** Otros
- **Label:** Cards

## Schema

| Campo | Tipo | Requerido | Default | Notas |
|-------|------|-----------|---------|-------|
| `title` | text | no | — | Título opcional del bloque |
| `description` | rich (basic) | no | — | Descripción / introducción del bloque |
| `items` | repeater | sí | 3 ítems | Lista de cards. Mínimo 1 ítem. |

### Campos de `items` (repeater)

| Campo | Tipo | Requerido | Default | Notas |
|-------|------|-----------|---------|-------|
| `title` | text | sí | — | Título de la card |
| `description` | textarea | no | — | Descripción breve (3 rows) |
| `image` | image | no | — | Imagen de la card (800x600) |
| `route` | route | no | — | Enlace opcional de la card. Soporta ancla. |

## Comportamiento
- Renderiza un grid de tarjetas (cards) con imagen opcional, título, descripción y enlace.
- El bloque puede tener un título y descripción general arriba del grid.
- Cada card en `items` puede enlazar a una ruta interna o externa.

## Notas de implementación
- El grid debe ser responsive: 1 columna en mobile, 2-3 en desktop según la cantidad de ítems.
- Si una card tiene `route`, toda la card debe ser clickeable (wrap en `<a>` o uso de `@click`).
- `image` debe mostrar un placeholder si no está definida.
- El botón "Agregar card" permite añadir más ítems desde el CMS.
