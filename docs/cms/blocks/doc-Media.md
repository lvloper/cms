# Block: Media

## Meta
- **Nombre:** Media
- **Categoría:** Multimedia
- **Label:** Imagen / Video

## Schema

| Campo | Tipo | Requerido | Default | Notas |
|-------|------|-----------|---------|-------|
| `media_type` | select (Video) | sí | — | Tipo de medio: `youtube`, `upload`, `image`. Forma parte del componente Video. |
| `youtube_id` | text | condicional | — | ID del video de YouTube (requerido si `media_type = youtube`) |
| `video_file` | file (video) | condicional | — | Archivo de video subido (requerido si `media_type = upload`) |
| `image` | image | condicional | — | Imagen subida (requerido si `media_type = image`). Directorio: `images/media` |
| `caption` | text | no | — | Epígrafe / pie de imagen o video |

## Comportamiento
- Muestra un medio visual (imagen, video de YouTube, o video subido) con epígrafe opcional.
- El tipo de medio se selecciona mediante `media_type`; los campos restantes se muestran condicionalmente.
- No tiene control de ancho propio — el contenedor padre define el tamaño.

## Notas de implementación
- Renderizar condicional según `$media_type`:
  - `youtube`: iframe embebido con `$youtube_id`.
  - `upload`: tag `<video>` con source apuntando a `$video_file`.
  - `image`: tag `<img>` con `$image`.
- `$caption` debe renderizarse como `<figcaption>` o similar debajo del medio.
- El directorio de imágenes es `images/media`.
