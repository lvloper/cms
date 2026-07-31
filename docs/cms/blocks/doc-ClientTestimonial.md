# ClientTestimonial — Props del bloque

Categoría: `Cliente`. Una o dos voces con retrato o video testimonial.

| Prop | Tipo | Requerido | Descripción |
|---|---|---:|---|
| `eyebrow` | `string` | no | Volanta. |
| `title` | `string` | no | Título de sección. |
| `testimonials` | `array` | sí | Uno o dos testimonios. |
| `testimonials[].quote` | `string` | sí | Cita exacta o extracto aprobado. |
| `testimonials[].person` | `string` | sí | Nombre. |
| `testimonials[].role` | `string` | sí | Cargo. |
| `testimonials[].media_*` | `media` | sí | Retrato/video, alt, placeholder y autoplay. |
