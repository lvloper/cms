# AGENTS.md

Guia operativa para que cualquier agente del proyecto interprete comandos cortos sin pedir rutas.

## Design system

- Todo subagente de diseno debe consultar `docs/ux/design-system.md` como referencia obligatoria.
- El design system es el unico punto de verdad para colores, tipografia, spacing, radios y reglas visuales.
- Ningun subagente debe hardcodear valores de marca (colores, fuentes, etc.).
- Para reutilizar este CMS en otro proyecto: reemplazar `docs/ux/design-system.md` y `tailwind.config.js`.

## Politica de modelo para diseno/UI

- Para los pasos de diseno (`design-prompt`, `design-from-html`), usar siempre el modelo de mayor capacidad disponible para tareas de diseno/UX/UI en el entorno.
- Prioridad sugerida: GPT-5.5 o equivalente de maxima capacidad.
- Fallback sugerido: GPT-5.3-Codex.
- Si no esta disponible, usar el mejor fallback disponible y dejar trazabilidad en la respuesta (`modelo_usado` + `motivo_fallback`).

## Defaults de enrutamiento

- Si el usuario dice `bloque`, `hero`, `cta`, `cards`, `quote`, `rich text`, `embed`, asumir dominio CMS Blocks.
- Ruta por defecto para bloques: `docs/cms/blocks/`.
- Ruta por defecto para subagentes de bloques: `docs/cms/blocks/subagents/`.
- Ruta por defecto para paginas CMS: `docs/cms/pages/`.

## Flujo estandar de bloques (4 pasos)

### 1. `draft` — Schema generico del bloque
- Ante pedido tipo: `necesito crear un bloque ...`
- Crear draft editable en `docs/cms/blocks/draft-<kebab>.md` usando la plantilla `docs/cms/blocks/draft.md`.
- Describe el schema del bloque: nombre, categoria, label, campos con tipo y reglas de validacion.
- No ejecutar implementacion en este paso.

### 2. `backend` — Clase PHP + vista con dump
- El usuario confirma el draft.
- El subagente `block-backend` genera:
  - `app/Filament/Blocks/{Name}Block.php` (clase con fields)
  - `resources/views/blocks/{Name}.blade.php` (vista con `@dump(get_defined_vars())`)
  - Registro en template correspondiente
- El draft se reemplaza por `docs/cms/blocks/doc-{Name}.md` (documentacion pura con props del schema bien documentados).
- El bloque es funcional desde el CMS, pero la vista solo muestra la data cruda.

### 3. `carga+valida` — Data real + validacion
- El usuario carga datos reales en el CMS (crea pagina, completa campos del bloque).
- El usuario informa al agente: "cargue tal y tal data".
- El subagente `data-validator` analiza los datos cargados vs el schema documentado en `doc-{Name}.md` y devuelve diagnostico.

### 4. `diseno` — Vista final (3 caminos)
- Una vez validada la data, se disena la vista final.
- **Camino A** `design-prompt`: descripcion textual del diseno -> genera Blade final.
- **Camino B** `design-from-html`: maqueta HTML de referencia -> ignora contenido, extrae estructura, mapea props del schema, genera Blade.
- **Camino C** (Pencil): para disenos grandes. Requiere tener el design system construido en Pencil primero.
- Opcional: pedir "genera la preview" para ejecutar `block-preview-capturer` y tener miniatura en el block picker.

## Comportamiento por defecto

- Si el usuario no da ruta y el contexto habla de bloques, NO preguntar ruta: usar `docs/cms/blocks/`.
- Si faltan datos, completar con placeholders `{{...}}` y marcar `pendiente`.
- Mantener un solo bloque por draft.
- No duplicar pagina existente cuando se indique reemplazo en pagina.

## Referencias obligatorias

- `docs/ux/design-system.md`
- `docs/cms/blocks/draft.md`
- `docs/cms/blocks/subagents/block-backend.md`
- `docs/cms/blocks/subagents/data-validator.md`
- `docs/cms/blocks/subagents/design-prompt.md`
- `docs/cms/blocks/subagents/design-from-html.md`
- `docs/cms/blocks/subagents/block-preview-capturer.md`

## Triggers recomendados (lenguaje natural)

- `redisenalo [bloque]` / `rediseña [bloque]` -> redesign: lee props actuales, genera nueva vista (sin tocar backend)
- `necesito crear un bloque ...` -> paso 1 (draft)
- `ya revise el draft` -> esperar confirmacion
- `esta ok, ejecuta` / `aprobado` -> paso 2 (backend)
- `ya cargue los datos` / `cargue tal y tal` -> paso 3 (carga+valida)
- `ahora disenalo` / `dame el diseno` / `maquetemoslo` -> paso 4 (diseno)
- `genera la preview` / `genera la miniatura` -> ejecuta `block-preview-capturer` sobre el bloque
- `te paso una maqueta` / `usa este HTML` -> infiere schema, crea draft para revisar (paso 1)
- `mejora la interfaz`, `mejorar UI`, `mejorar UX`, `mejorar diseno` (o similar) -> usar `docs/cms/blocks/subagents/design-prompt.md`

## Inventario de bloques

- Usar `php artisan cms:blocks-list` para listar todos los bloques del proyecto (PHP + Blade).
- Opciones: `--orphans` (solo vistas sin clase PHP), `--unregistered` (solo clases no registradas en templates), `--json` (salida JSON para consumo agente).
- Los bloques sin clase PHP son vistas frontend reutilizables pero no tienen backend en el CMS.

## Acceso al panel

- En desarrollo, las credenciales por defecto del panel `/admin` se definen via variables de entorno o seeders. Consultar `README.md` o `.env.example` para credenciales de desarrollo.
