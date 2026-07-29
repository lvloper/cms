# Design System

## Principios
- Mobile-first responsive
- Accesible (WCAG AA)
- Tokens via CSS custom properties
- La interfaz parte siempre de fondo negro y texto blanco.
- Las páginas editoriales principales usan rutas Inertia y componentes React estáticos.
- El sistema de bloques queda reservado para contenido que realmente necesite composición dinámica, como proyectos o recursos similares.

## Colors
```css
--color-surface       /* Fondo base: negro */
--color-text          /* Texto base: blanco */
--color-primary
--color-primary-hover
--color-secondary
--color-secondary-hover
--color-secondary-light
--color-black
--color-white
--color-gray
--color-gray-2
--color-gray-3

/* Colores del logo y acentos de Socies */
--color-socies-green
--color-socies-blue
--color-socies-yellow
--color-socies-coral
--color-socies-violet
--color-socies-aqua
```

- No usar blanco como fondo general del sitio.
- El color se reserva para el logo, indicadores y acentos puntuales.
- Los estados monocromos del logo usan círculos blancos y glifos negros.

## Typography
```css
font-family: 'Manrope', sans-serif;  /* Default body */
font-family: 'Poppins', sans-serif;  /* Alternativa */
font-family: 'Gotham Light', 'Gotham', sans-serif; /* Hero home */
```

## Spacing
- `container` centered with responsive padding
- Section padding: `py-12 md:py-16`
- Grid gaps: `gap-6 md:gap-8`

## Componentes base
- `<HomeHero>` — hero estático de la home, editable mediante constantes del componente React.
- `<SociesLogo>` — SVG compartido por el hero y el header.
- `<SiteHeader>` — header global de las páginas Inertia.
- `<x-block>` — wrapper reservado para recursos administrables mediante bloques.
- `<x-link>` — link que maneja rutas internas/externas/anclas en vistas Blade.
- `<x-layout>` — layout de páginas Blade/CMS.

## Breakpoints
```
xs: 480px
sm: 640px
md: 768px
lg: 1024px
xl: 1080px
2xl: 1280px
```

## Reglas
- No hardcodear valores de marca. Usar clases Tailwind (`text-primary`, `bg-white`, etc.)
- El fondo por defecto es negro y el texto por defecto es blanco.
- La home no se administra con el page builder: su contenido se edita en el componente React correspondiente.
- Todo elemento visual perteneciente a un recurso CMS debe ser editable desde el CMS.
- Usar `container mx-auto px-4` para centrar contenido

## Hero de la home

- Ocupa el viewport completo y no incluye bajada.
- En cada carga completa de la home reproduce la presentación completa y bloquea el scroll hasta finalizar.
- La `S` verde cae desde arriba, impacta y realiza exactamente dos rebotes pequeños.
- Las letras restantes nacen desde la posición de la `S` y forman el logo centrado.
- El logo pasa a su versión monocromática, se ubica cerca del borde inferior y queda por encima de una línea blanca de `1px` que crece desde su centro.
- El título usa Gotham Light, mayúsculas y un tamaño contenido.
- El copy del título rota en verde Socies entre `sistemas`, `soluciones`, `diseños`, `experiencias` y `productos`.
- La rotación acelera en intervalos de `1000ms`, `850ms`, `700ms`, `550ms`, `400ms` y `300ms`; al llegar al mínimo, el ciclo vuelve a `1000ms`.
- El título aparece letra por letra y renglón por renglón según el wrapping real de cada pantalla; las máscaras deben conservar las descendentes tipográficas.
- La flecha usa el ícono `arrow-down-right` de Lucide. La flecha y el círculo final ocupan una grilla cuadrada del mismo tamaño que el texto; el círculo respeta el mismo padding óptico `7/24` del ícono.
- Al hacer scroll, el logo y la línea acompañan el recorrido hacia el header. En el punto de encuentro se intercambia instantáneamente por el logo del header, sin fade.
- Al comenzar ese recorrido, el bloque de texto se desplaza hacia arriba y sale del hero; no queda fijado junto al logo.
- El recorrido fijado no agrega una pantalla vacía: el contenido siguiente avanza durante el handoff y aparece inmediatamente después de la línea, con el spacing de sección.
- Al volver a la home mediante navegación Inertia se omite la presentación principal y sólo se reproduce el título. Una recarga completa vuelve a ejecutar toda la intro.
- Si pasan `4s` desde el final de la animación sin que el usuario haya scrolleado, aparece un `chevron-down` verde Socies con rebote, pegado debajo del título y con el mismo tamaño tipográfico. Se descarta definitivamente al acumular los primeros `300px` de scroll.
- En `prefers-reduced-motion` se muestra directamente el estado final.
- La animación completa se conserva en mobile, con medidas y offsets adaptativos.
