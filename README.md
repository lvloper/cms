# Socies CMS

Socies CMS es el CMS que utilizamos en los proyectos de **socies.agency** para construir sitios modernos, escalables y faciles de administrar.

Esta base nace con un objetivo claro: acelerar entregas sin sacrificar calidad. Combina una experiencia editorial visual (drag & drop), una arquitectura solida sobre Laravel y un flujo de desarrollo potenciado por LLMs para iterar mas rapido, mantener consistencia y reducir friccion en cada release.

## Propuesta de valor

- **Administracion simple para equipos no tecnicos**: panel intuitivo para publicar, editar y mantener contenido sin depender del equipo de desarrollo.
- **Constructor modular drag & drop**: paginas armadas por bloques reutilizables para escalar contenido y campañas con autonomia.
- **Base robusta para crecimiento**: pensado para sitios corporativos, landings, blogs y ecosistemas de contenido en evolucion constante.
- **Desarrollo potenciado por LLMs**: estructura y convenciones que facilitan implementar features, automatizar tareas y acelerar time-to-market.
- **SEO y gobernanza integrados**: metadata por ruta, sitemap, roles/permisos y trazabilidad de cambios desde el inicio.

## Funcionalidades

- **Sistema de rutas**: enrutamiento jerarquico con gestion de slugs.
- **Sistema de bloques**: page builder modular con editor enriquecido (TipTap).
- **Blog**: gestion de publicaciones, tags y rutas.
- **Menus**: administracion centralizada de navegacion.
- **Redirecciones**: manejo de redirecciones 301/302 con cache.
- **Configuracion**: parametros globales en formato clave/valor.
- **Banners**: gestion de banners por ubicacion.
- **Usuarios y roles**: control de acceso con Filament Shield.
- **SEO**: metadata SEO incorporada por ruta.
- **Activity log**: trazabilidad de acciones con Spatie Activity Log.
- **Sitemap**: generacion automatica de `sitemap.xml`.
- **Conversación Socies**: captación breve de consultas en home y `/hablemos`, con campañas, precarga privada, playbooks y reglas de alcance editables desde Filament.

## Conversación Socies

El bootstrap crea 14 intenciones, 18 preguntas, 15 respuestas aprobadas, 7 playbooks, 14 reglas de servicio y 5 campañas. Los seeds son idempotentes y no pisan textos editados desde el CMS.

```bash
php artisan db:seed --class=PacoBootstrapSeeder
php artisan paco:prefill-link direct_default \
  --name="Ana" \
  --email="ana@ejemplo.com" \
  --query="Necesitamos automatizar una aprobación" \
  --source=email \
  --medium=newsletter
```

Los enlaces precargados vencen según `PACO_PREFILL_TTL_MINUTES`, se consumen una sola vez y nunca exponen nombre, contacto o consulta en la URL. Campañas, playbooks, respuestas, reglas y consultas recibidas se administran en `/admin`, grupo **Conversaciones**.

### Proveedor de IA

OpenCode Go queda seleccionado con `mimo-v2.5` en `opencode.json`. Completá `OPENCODE_API_KEY` en `.env` para activarlo. `OPENCODE_MAX_TOKENS` queda en 1600 para permitir que modelos con razonamiento interno completen la salida JSON. Si la clave falta, OpenCode Go devuelve un error de cuota o el resultado no cumple el JSON esperado, Paco usa automáticamente el gateway determinista durante el cooldown configurado en `PACO_AI_FALLBACK_COOLDOWN_MINUTES`.

Para reintentar el proveedor después de un límite, limpiá el circuito desde Tinker:

```bash
php artisan tinker --execute="Cache::forget('paco:opencode-go:circuit-open');"
```

## Stack tecnologico

- PHP 8.3+
- Laravel 13
- Filament 5
- Livewire 4
- Alpine.js
- Tailwind CSS 3
- Vite 8
- TipTap Editor

## Puesta en marcha

### Requisitos

- PHP 8.3+
- Composer 2+
- Node.js 20+
- npm 10+
- PostgreSQL

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install && npm run build
```

## Panel de administracion

Accede al panel en `/admin`.
