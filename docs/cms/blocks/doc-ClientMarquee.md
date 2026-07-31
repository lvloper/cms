# ClientMarquee — Props del bloque

Categoría: `Cliente`. Pausa tipográfica animada para frases editoriales sobre el trabajo y el acompañamiento.

| Prop | Tipo | Requerido | Default | Descripción |
|---|---|---:|---|---|
| `items` | `string[]` | sí | `[]` | Entre 3 y 6 frases largas. |
| `speed` | `slow\|medium` | sí | `slow` | Duración del recorrido. |
| `direction` | `left\|right` | sí | `left` | Sentido del movimiento. |

Se duplica visualmente la lista para lograr continuidad; la copia se oculta a tecnologías asistivas y la animación se pausa con reducción de movimiento.
