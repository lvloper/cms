# ClientFeature — Props del bloque

Categoría: `Cliente`. Narrativa multimedia con texto sticky, apta para plataformas, proyectos o sistemas sin convertirlos en otro caso completo.

| Prop | Tipo | Requerido | Descripción |
|---|---|---:|---|
| `eyebrow` | `string` | no | Volanta. |
| `title` | `string` | sí | Título del proyecto. |
| `body` | `html` | no | Texto enriquecido. |
| `outcome` | `string` | no | Valor o resultado destacado. |
| `layout` | `text_left\|text_right` | sí | Ubicación del texto; default `text_left`. |
| `media` | `array` | sí | Secuencia de 1 a 4 piezas. |
| `media[].label` | `string` | no | Nombre interno. |
| `media[].media_*` | `media` | sí | Imagen/video, alt, placeholder y autoplay. |
| `media[].caption` | `string` | no | Epígrafe. |

En desktop el texto permanece sticky mientras se recorre la secuencia multimedia. En mobile se conserva el orden lógico texto → media.
