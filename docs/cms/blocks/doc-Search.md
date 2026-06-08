# Block: Search

## Meta
- **Nombre:** Search
- **Categoría:** Contenido
- **Label:** Búsqueda

## Schema

| Campo | Tipo | Requerido | Default | Notas |
|-------|------|-----------|---------|-------|
| `title` | text | no | `Buscar` | Título del bloque de búsqueda |
| `description` | textarea | no | `Buscá en el sitio lo que necesitás.` | Texto descriptivo (2 rows) |

## Comportamiento
- Renderiza un bloque con un campo de búsqueda, título y descripción.
- Al enviar, redirige a la página de resultados de búsqueda del sitio con el query en la URL.
- No requiere configuración adicional — el comportamiento de búsqueda es global del sitio.

## Notas de implementación
- El formulario debe ser un `<form>` con `method="GET"` apuntando a la ruta de resultados de búsqueda.
- El input debe tener `name="q"` o el nombre que use el motor de búsqueda del sitio.
- `title` y `description` son personalizables desde el CMS.
- Considerar agregar un ícono de lupa dentro del input.
