# ClientStatement — Props del bloque

Categoría: `Cliente`. Declaración humana de gran escala acompañada por una pieza visual.

| Prop | Tipo | Requerido | Descripción |
|---|---|---:|---|
| `eyebrow` | `string` | no | Volanta. |
| `title` | `string` | sí | Declaración principal. |
| `body` | `html` | no | Texto enriquecido. |
| `layout` | `text_left\|text_right` | sí | Ubicación del texto; default `text_left`. |
| `media_type` | `image\|video` | sí | Tipo de media. |
| `media_image` | `path` | condicional | Imagen. |
| `media_video` | `path` | condicional | Video. |
| `media_alt` | `string` | no | Descripción accesible. |
| `media_placeholder` | `string` | si falta archivo | Texto del placeholder gris. |
| `media_autoplay` | `bool` | no | Autoplay silenciado. |

Texto y media se centran entre sí sobre el eje vertical en desktop.
