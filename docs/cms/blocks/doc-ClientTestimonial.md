# ClientTestimonial — Props del bloque

Categoría: `Cliente`. Una voz textual tomada del tab de testimonios del cliente.

| Prop | Tipo | Requerido | Descripción |
|---|---|---:|---|
| `eyebrow` | `string` | no | Volanta. |
| `title` | `string` | no | Título de sección. |
| `testimonials` | `array` | sí | Un único testimonio. |
| `testimonials[].quote` | `string` | sí | Texto literal completo enviado por el cliente. Puede conservar saltos de párrafo. |
| `testimonials[].person` | `string` | sí | Nombre. |
| `testimonials[].role` | `string` | sí | Cargo. |

El bloque no agrega imágenes ni videos: la evidencia testimonial se presenta como texto firmado para respetar el contenido original del cliente.
