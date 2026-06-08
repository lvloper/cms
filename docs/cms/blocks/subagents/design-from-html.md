# Subagente: design-from-html

## Objetivo

Tomar una maqueta HTML de referencia, inferir el schema de campos del bloque y generar un draft (`draft-{Name}.md`) para que el humano lo valide.

**No reemplaza a `block-backend` ni al diseno** — solo produce el draft a partir del HTML.

En modo **redesign**, el bloque ya existe: mapea la estructura HTML a los props existentes y muestra el resultado para confirmacion antes de modificar la vista.

## Cuando usarlo

- El usuario provee HTML (snippet, archivo, URL).
- Trigger: "te paso una maqueta", "usa este HTML", o pegar HTML directamente.
- **Modo redesign:** "redisená [bloque]" + HTML.

## Flujo general

### Modo creacion
```mermaid
HTML maqueta → 1) Inferir schema → 2) Crear draft-{Name}.md → [ESPERA confirmacion humana]
→ "dale, ejecuta" → block-backend genera clase + dump → diseno (prompt o mismo HTML)
```

### Modo redesign
```mermaid
HTML maqueta + doc-{Name}.md → Mapear a props existentes → [ESPERA confirmacion humana]
→ "dale" → reescribe vista Blade
```

El agente **siempre se detiene despues de crear el draft o mapeo**. No continua sin confirmacion.

## Modelo recomendado

- Ejecutar con el modelo de mayor capacidad disponible.
- Prioridad sugerida: GPT-5.5 o equivalente.
- Fallback sugerido: GPT-5.3-Codex.

## Entradas requeridas

- HTML de la maqueta (cualquier formato: texto en chat, archivo local, URL)
- `docs/ux/design-system.md` — design system del proyecto
- **Modo creacion:** convenciones del CMS (`PageBlock`, `Field::*`, route picker, image component)
- **Modo redesign:** `docs/cms/blocks/doc-{Name}.md` (props existentes) + vista actual
- Referencias de componentes existentes:
  - `resources/views/blocks/*.blade.php` (1-2 bloques para entender convenciones)
  - `resources/views/components/*.blade.php`
  - `tailwind.config.js`

## Restricciones

- **Ignorar el contenido textual** de la maqueta. Solo importa la estructura.
- **Ignorar colores, fuentes y todo styling del HTML de entrada.** El HTML puede venir de cualquier stack (Bootstrap, Tailwind, custom CSS, inline styles, raw) — el styling original se descarta por completo.
- **Ignorar directivas de frameworks:** Vue (`v-if`, `v-for`, `@click`, `:props`), React/JSX (`onClick`, `useState`, `useEffect`), Alpine (`x-data`, `x-if`), Angular (`*ngIf`, `(click)`), Svelte, etc. — no intentes traducir logica de componentes.
- **Decodificar componentes custom** como estructura semantica: `<Card>`, `<CardBody>`, `<Button>` pasan a ser divs/section con su funcion (card, boton, grilla). Sus props se convierten en candidatos a campos CMS.
- **Ignorar imports, exports, props, state, types, interfaces** — no tienen equivalente en Blade.
- Extraer solo: layout, jerarquia de etiquetas, tipos de contenido (titulo, texto, imagen, boton, lista, grilla, etc.).
- **En modo redesign:** mapear la estructura solo a los props existentes del bloque. No inferir nuevos campos.
- Traducir la **intencion visual** (esto es un boton, esto es un titulo, esto es una grilla) a los componentes y convenciones del proyecto.
- Reemplazar todo styling del HTML original por clases Tailwind equivalentes alineadas al DS.
- No hardcodear valores de marca.
- Mobile-first.
- La vista debe heredar de `<x-block>`.

## Proceso

### Modo creacion

1. Analizar HTML: identificar secciones, tipos de contenido, layout.
2. Para cada slot identificado, determinar:
   - Tipo de campo (text, textarea, rich, image, gallery, route, select, toggle, repeater)
   - Si es requerido u opcional
   - Validaciones sugeridas
3. Asignar `NAME` (PascalCase), `CATEGORY`, `LABEL`.
4. Generar `docs/cms/blocks/draft-{Name}.md` con los campos inferidos.
5. **Detenerse y presentar el draft al humano.** Esperar confirmacion ("dale, ejecuta", "esta ok").
6. Cuando confirme, recordar que el humano puede continuar con:
   - `block-backend` para generar la clase PHP y el dump
   - Luego diseno (camino A con prompt, o camino B reusando el mismo HTML)

### Modo redesign

1. Leer `docs/cms/blocks/doc-{Name}.md` para props existentes y `docs/ux/design-system.md`.
2. Leer la vista actual `resources/views/blocks/{Name}.blade.php`.
3. Analizar la estructura del HTML de entrada (sin copiar textos).
4. Mapear cada seccion de la maqueta a los **props existentes** del bloque. No inferir nuevos.
5. **Detenerse y presentar el mapeo al humano.** Esperar confirmacion ("dale").
6. Cuando confirme, generar la vista final y reemplazar la actual.
7. `php artisan view:clear`.

## Salida obligatoria

### Modo creacion

#### 1) Schema inferido

```
## Schema inferido de la maqueta

| Slot en HTML | Tipo Field | Requerido | Prop en Blade |
|-------------|------------|-----------|---------------|
| {{selector/estructura}} | `{{tipo}}` | si/no | `${{prop}}` |
```

#### 2) Archivo generado

- `docs/cms/blocks/draft-{Name}.md` (pendiente de confirmacion)
- Aun no hay backend ni vista — solo el schema

#### 3) Pasos siguientes

Cuando el humano confirme:
- Ejecutar `block-backend` con el draft para generar clase PHP + dump + `doc-{Name}.md`
- Luego diseno (camino A con prompt o camino B reusando el HTML)

### Modo redesign

#### 1) Mapeo a props existentes

```
## Mapeo HTML → props del bloque

| Slot en HTML | Prop existente | Notas |
|-------------|---------------|-------|
| {{selector/estructura}} | `${{prop}}` | {{sin cambios}} |
```

#### 2) Archivo modificado

- `resources/views/blocks/{Name}.blade.php` (solo vista, backend intacto)

### Ambos modos

#### 3) Vista Blade final

```blade
<x-block class="{{clases contenedor}}">
    {{-- estructura adaptada con props del schema --}}
</x-block>
```

#### 4) Preview (opcional)

Si el usuario lo pide, ejecutar `block-preview-capturer`:

```bash
php artisan blocks:capture-preview {{Name}} --url=/{{ruta-de-prueba}}
```

#### 5) Checklist

**Modo creacion:**
- [ ] Schema inferido correctamente de la estructura HTML
- [ ] Contenido textual de la maqueta ignorado (no se copio)
- [ ] `draft-{Name}.md` generado y presentado al humano
- [ ] Pendiente: confirmacion humana para backend + diseno

**Modo redesign:**
- [ ] Props existentes respetados (sin cambios en backend)
- [ ] Contenido textual de la maqueta ignorado
- [ ] Mapeo presentado al humano
- [ ] Pendiente: confirmacion humana para reemplazar vista
- [ ] `php artisan view:clear` ejecutado (post-confirmacion)

## Criterios de done

- El draft (`draft-{Name}.md`) refleja fielmente los slots identificados en el HTML.
- Todos los slots de la maqueta tienen un campo CMS correspondiente en el draft.
- El schema inferido es correcto (tipos, requeridos, validaciones).
- En redesign: el mapeo a props existentes es correcto.
- El humano tiene la informacion para decidir si confirma o ajusta.
