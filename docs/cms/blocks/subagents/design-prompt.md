# Subagente: design-prompt

## Objetivo

Tomar la documentacion de props de un bloque (`doc-{Name}.md`) y una descripcion textual del diseno, y generar la vista Blade final con estilos, respetando el design system del proyecto.

## Cuando usarlo

- **Modo creacion:** el bloque no tiene vista disenada aun (viene de backend + dump).
- **Modo redesign:** el bloque ya tiene una vista, se quiere reemplazar manteniendo los props.
- Trigger: "ahora disenalo", "dame el diseno", "maquetemoslo", "redisenalo", o descripcion textual directa.

## Modelo recomendado

- Ejecutar con el modelo de mayor capacidad disponible para diseno/maquetacion.
- Prioridad sugerida: GPT-5.5 o equivalente.
- Fallback sugerido: GPT-5.3-Codex.
- Si se usa fallback, reportar `modelo_usado` y `motivo_fallback`.

## Entradas requeridas

- `docs/cms/blocks/doc-{Name}.md` — props del bloque, schema y reglas de renderizado
- `docs/ux/design-system.md` — design system del proyecto (obligatorio)
- Descripcion textual del diseno deseado por el usuario
- Referencias de componentes existentes:
  - `resources/views/blocks/*.blade.php` (1-2 bloques para entender convenciones)
  - `resources/views/components/*.blade.php` (componentes reutilizables: `<x-block>`, `<x-link>`, etc.)
  - `tailwind.config.js` (tokens configurados)
- **En modo redesign:** ademas, la vista actual `resources/views/blocks/{Name}.blade.php`

## Restricciones

- No hardcodear valores de marca: usar clases Tailwind que correspondan a los tokens del DS.
- Mobile-first siempre.
- Todo elemento visual debe mapearse a un prop del schema.
- No agregar contenido fantasia ni texto placeholder en la vista — los props se renderizan dinamicamente.
- Respetar reglas del design system: colores, tipografia, spacing, radios, etc.
- La vista debe heredar de `<x-block>` y seguir las convenciones de los bloques existentes.
- **En modo redesign:** no cambiar los props ni el nombre del bloque. Solo la vista.

## Proceso

1. Leer `doc-{Name}.md` para entender props disponibles.
2. Leer `docs/ux/design-system.md` para colores, tipografia, spacing, reglas.
3. Leer 1-2 bloques existentes como referencia de convenciones Blade.
4. **En modo redesign:** ademas, leer la vista actual para conocer la estructura a reemplazar.
5. Disenar estructura mobile-first usando los props del schema.
6. Aplicar clases Tailwind alineadas al DS.
7. Generar vista final en `resources/views/blocks/{Name}.blade.php`.
8. Ejecutar `php artisan view:clear`.

## Salida obligatoria

### 1) Vista Blade final

```blade
<x-block class="{{clases contenedor}}">
    <div class="container mx-auto {{clases}}">
        @if($title ?? false)
            <h2 class="{{clases tipografia}}">{{ $title }}</h2>
        @endif
        {{-- resto de props --}}
    </div>
</x-block>
```

### 2) Preview (opcional)

Si el usuario lo pide, ejecutar `block-preview-capturer`:

```bash
php artisan blocks:capture-preview {{Name}} --url=/{{ruta-de-prueba}}
```

### 3) Checklist

- [ ] Todos los props del schema estan representados en la vista
- [ ] Diseno mobile-first
- [ ] Clases Tailwind alineadas al DS
- [ ] Sin contenido hardcodeado (todo viene de props)
- [ ] `php artisan view:clear` ejecutado

## Criterios de done

- La vista renderiza correctamente todos los props del bloque.
- El diseno sigue el design system del proyecto.
- No hay contenido fantasia — los valores se renderizan desde la data del CMS.
- La vista es responsiva.
