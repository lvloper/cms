# ClientProcess — Props del bloque

Categoría: `Cliente`. Diagrama editorial para procesos, integraciones o partes de un ecosistema.

| Prop | Tipo | Requerido | Descripción |
|---|---|---:|---|
| `eyebrow` | `string` | no | Volanta. |
| `title` | `string` | sí | Título de sección. |
| `body` | `string` | no | Explicación breve. |
| `nodes` | `array` | sí | Entre 2 y 12 nodos ordenados. |
| `nodes[].label` | `string` | sí | Nombre del nodo. |
| `nodes[].detail` | `string` | no | Función o detalle. |

El diagrama es contenido visual por sí mismo y conserva un orden de lista semántico.
