# ClientMetrics — Props del bloque

Categoría: `Cliente`. Métricas editoriales con una pieza contextual.

| Prop | Tipo | Requerido | Descripción |
|---|---|---:|---|
| `eyebrow` | `string` | no | Volanta. |
| `title` | `string` | sí | Título. |
| `body` | `string` | no | Texto breve. |
| `layout` | `text_left\|text_right` | sí | Ubicación del texto; default `text_left`. |
| `metrics` | `array` | sí | Entre 1 y 4 métricas. |
| `metrics[].value` | `string` | sí | Valor visible. |
| `metrics[].label` | `string` | sí | Significado. |
| `metrics[].note` | `string` | no | Fuente, estado o advertencia. |
| `media_*` | `media` | sí | Imagen/video, alt, placeholder y autoplay. |

Las cifras pendientes deben indicarlo en `note`. Al entrar en viewport se incrementa la parte numérica preservando prefijos, sufijos y separadores (`30+`, `~10.000`, `24/7`); con reducción de movimiento se muestra el valor final directamente.
