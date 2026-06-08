# Block: MediaText

## Meta
- **Nombre:** MediaText
- **Categoría:** Contenido
- **Label:** Imagen y texto

## Schema

| Campo | Tipo | Requerido | Default | Notas |
|-------|------|-----------|---------|-------|
| `layout` | toggleButtons | sí | `left` | Disposición: `left` (Imagen a la izquierda), `right` (Imagen a la derecha) |
| `media_type` | select (Video) | sí | — | Tipo de medio: `youtube`, `upload`, `image`. Forma parte del componente Video. |
| `youtube_id` | text | condicional | — | ID del video de YouTube (requerido si `media_type = youtube`) |
| `video_file` | file (video) | condicional | — | Archivo de video subido (requerido si `media_type = upload`) |
| `image` | image | condicional | — | Imagen subida (requerido si `media_type = image`). Directorio: `images/media-text` |
| `title` | text | no | — | Título del bloque |
| `content` | rich (basic) | no | — | Texto con editor básico (formato limitado) |
| `cta` | route | no | — | Botón de llamada a la acción opcional |

## Comportamiento
- Muestra una disposición de dos columnas: medio visual a un lado y contenido textual al otro.
- `layout` controla si el medio va a la izquierda o a la derecha.
- `cta` es un picker de ruta interna (página CMS, URL externa, o ancla).

## Notas de implementación
- Estructura responsive: en mobile apilar verticalmente (medio arriba, texto abajo) independientemente de `layout`.
- El medio se renderiza igual que en MediaBlock (iframe/video/img según `media_type`).
- `cta` debe renderizarse como `<a>` con la URL resuelta. Si `cta` tiene `anchor`, usar ese valor como fragmento.
- El directorio de imágenes es `images/media-text`.
