# Funciones genéricas del CMS (muy breve)

## 1) Enrutamiento y render dinámico
- Resuelve cualquier URL por slug y la renderiza según el contenido/ruta publicada.
- Incluye buscador, sitemap XML y fallback global `/{slug?}`.
- Permite controladores personalizados por tipo de contenido (ej: Blog).

**Citas:** `routes/web.php`, `app/Http/Controllers/RouteController.php`, `app/Http/Controllers/SearchController.php`, `app/Http/Controllers/SitemapController.php`, `app/Models/Route.php`, `config/cms-routes.php`

## 2) Page Builder por bloques
- Editor modular con `Builder` de Filament: agrega, ordena, clona y previsualiza bloques.
- Cada bloque comparte pestañas de contenido/general/avanzado (ancla, clases, estilos, ocultar).
- Render en frontend por tipo de bloque (`blocks.{type}`) con soporte de estilos por bloque.

**Citas:** `app/Filament/Templates/DefaultTemplate.php`, `app/Filament/Templates/ModalTemplate.php`, `app/Filament/Blocks/PageBlock.php`, `app/Filament/Blocks/BaseRichTextBlock.php`, `app/Filament/Blocks/BaseCtaBlock.php`, `resources/views/components/blocks.blade.php`, `resources/views/blocks/BaseRichText.blade.php`

## 3) Recursos Filament (CRUD CMS)
- Recursos base para contenido con tabs de contenido + configuración SEO/ruta/layout.
- Módulos CMS genéricos: páginas, blog, menús, redirecciones, configuraciones, banners, usuarios.
- Lista/admin con filtros, acciones de preview y utilidades de import/export donde aplica.

**Citas:** `app/Filament/Resources/Bases/ResourceBase.php`, `app/Filament/Traits/HasRoute.php`, `app/Filament/Resources/PageResource.php`, `app/Filament/Resources/BlogResource.php`, `app/Filament/Resources/MenuResource.php`, `app/Filament/Resources/RedirectionResource.php`, `app/Filament/Resources/ConfigurationResource.php`, `app/Filament/Resources/BannerResource.php`

## 4) Recursos personalizados reutilizables
- Componentes de formulario reutilizables: selector de rutas, imágenes (desktop/mobile), galerías e iconos.
- Enlaces inteligentes: interno, externo, ancla, archivo descargable, modal.
- Componentes Blade reutilizables para layout, bloques, header/menu, imágenes y links.
- Componentes custom de Filament:
  `app/Filament/Forms/Components/RoutePicker.php`, `Image.php`, `Gallery.php`, `IconPicker.php`.
- Vista custom esperada para `RoutePicker`:
  `resources/views/filament/forms/components/route-picker.blade.php`.

**Citas:** `app/Filament/Forms/Components/RoutePicker.php`, `app/Filament/Forms/Components/Image.php`, `app/Filament/Forms/Components/Gallery.php`, `app/Filament/Forms/Components/IconPicker.php`, `resources/views/filament/forms/components/route-picker.blade.php`, `resources/views/components/link.blade.php`, `resources/views/components/image.blade.php`, `resources/views/components/layout.blade.php`, `resources/views/components/common/header.blade.php`, `resources/views/pages/blocksList.blade.php`

## 5) Capacidades CMS “core” de datos
- `Route`: árbol jerárquico de URLs, estado publicación, SEO y sitemap.
- `Menu`: estructura anidada de navegación con orden normalizado.
- `Redirection`: gestión de 301/302/etc con normalización y cache.
- `Configuration` y `Banner`: key/value tipado y banners por ubicación/estado.

**Citas:** `app/Models/Route.php`, `app/Models/Menu.php`, `app/Models/Redirection.php`, `app/Models/Configuration.php`, `app/Models/Banner.php`, `README.md`
