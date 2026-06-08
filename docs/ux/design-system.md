# Design System

## Principios
- Mobile-first responsive
- Accesible (WCAG AA)
- Tokens via CSS custom properties

## Colors
```css
--color-primary       /* Color principal (botones, links, acentos) */
--color-primary-hover /* Hover del color principal */
--color-secondary     /* Color secundario */
--color-secondary-hover
--color-secondary-light
--color-black
--color-white
--color-gray          /* Gris base */
--color-gray-2        /* Gris claro */
--color-gray-3        /* Gris fondo */
```

## Typography
```css
font-family: 'Manrope', sans-serif;  /* Default body */
font-family: 'Poppins', sans-serif;  /* Alternativa */
```

## Spacing
- `container` centered with responsive padding
- Section padding: `py-12 md:py-16`
- Grid gaps: `gap-6 md:gap-8`

## Componentes base
- `<x-block>` — wrapper section para bloques
- `<x-link>` — link que maneja rutas internas/externas/anclas
- `<x-layout>` — layout de página
- `<x-common.header>` — menú header
- `<x-common.footer>` — footer

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
- Todo elemento visual debe ser editable desde el CMS
- Usar `container mx-auto px-4` para centrar contenido
