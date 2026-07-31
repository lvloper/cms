# ClientClosing — Props del bloque

Categoría: `Cliente`. Mosaico final y CTA en un único cierre editorial.

| Prop | Tipo | Requerido | Descripción |
|---|---|---:|---|
| `eyebrow` | `string` | no | Volanta. |
| `title` | `string` | sí | Título de cierre. |
| `body` | `string` | no | Texto breve. |
| `media` | `array` | sí | Entre 1 y 8 piezas. |
| `media[].label` | `string` | no | Nombre interno. |
| `media[].media_*` | `media` | sí | Imagen/video, alt, placeholder y autoplay. |
| `cta` | `route` | no | Enlace interno, externo, ancla o archivo. |

El CTA se omite cuando no tiene destino válido.
