# ClientProjects — Props del bloque

Categoría: `Cliente`. Pista horizontal para 1 a 3 experiencias representativas.

| Prop | Tipo | Requerido | Descripción |
|---|---|---:|---|
| `eyebrow` | `string` | no | Volanta. |
| `title` | `string` | sí | Título de sección. |
| `intro` | `string` | no | Introducción breve. |
| `projects` | `array` | sí | Proyectos reordenables, máximo 3. |
| `projects[].eyebrow` | `string` | no | Tipo o contexto. |
| `projects[].title` | `string` | sí | Nombre del proyecto. |
| `projects[].summary` | `string` | sí | Descripción breve. |
| `projects[].tags` | `string[]` | no | Capacidades. |
| `projects[].media_*` | `media` | sí | Imagen/video, alt, placeholder y autoplay según contrato multimedia. |

En desktop la sección queda fijada mientras el scroll vertical desplaza la pista horizontal mediante GSAP. La barra horizontal nunca se muestra. En mobile y con reducción de movimiento los proyectos se apilan para conservar un recorrido lineal.
