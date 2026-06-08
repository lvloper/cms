# Subagente: block-preview-capturer

## Objetivo

Generar una captura visual real de un bloque CMS ya implementado y guardarla como miniatura reusable para el selector de bloques de Filament.

## Entradas requeridas

- `block_name`: nombre del bloque CMS, por ejemplo `Hero`.
- `page_slug_o_ruta`: URL donde el bloque ya renderiza, por ejemplo `/home` o `/`.
- Selector esperado del bloque: `.block-{block_name}`.

## Restricciones

- No modificar el contenido editorial de la pagina.
- No capturar la pagina completa si existe el bloque solicitado.
- Guardar la imagen en `public/img/admin/blocks/{block_name}.png`.
- La imagen debe ser liviana, horizontal y representativa del bloque.

## Proceso

1. Verificar que la pagina renderiza HTTP 200.
2. Localizar el bloque con `.block-{block_name}`.
3. Capturar solo ese bloque con navegador headless.
4. Guardar la salida en `public/img/admin/blocks/{block_name}.png`.
5. Confirmar que el selector de bloques la detecta automaticamente.

## Comando recomendado

```bash
php artisan blocks:capture-preview Hero --url=/home
```

## Salida obligatoria

- Ruta de imagen generada.
- URL fuente usada.
- Selector usado.
- Estado de verificacion.

## Criterios de done

- Existe `public/img/admin/blocks/{block_name}.png`.
- El bloque aparece con miniatura en el block picker de Filament.
- El render publico sigue funcionando.
