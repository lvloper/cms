# Draft de bloque: Hero

## Meta
- **Nombre:** Hero
- **Categoría:** Contenido
- **Label:** Hero

## Schema

| Campo | Tipo | Requerido | Default | Notas |
|-------|------|-----------|---------|-------|
| title | text | sí | - | Título principal del hero |
| subtitle | textarea | no | - | Bajada o descripción |
| buttonText | text | no | - | Texto del botón |
| buttonLink | route | no | - | Enlace del botón |

## Comportamiento
- Bloque de presentación visual con título, bajada y botón CTA.
- El botón es opcional: si no se completa buttonText y buttonLink, no se renderiza.

## Notas de implementación
- Usar Field::text para title.
- Usar Field::textarea para subtitle.
- Usar Field::text + Field::route para el botón.
