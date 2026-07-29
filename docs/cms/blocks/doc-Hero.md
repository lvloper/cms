# Hero — Props del bloque

## Schema

| Prop | Tipo | Requerido | Default | Descripcion |
|------|------|-----------|---------|-------------|
| `title` | `text` | si | - | Título principal del hero |
| `subtitle` | `textarea` | no | - | Bajada o descripción |
| `buttonText` | `text` | no | - | Texto del botón |
| `buttonLink` | `route` | no | - | Enlace del botón |

## Contrato de datos

```json
{
  "type": "Hero",
  "data": {
    "title": "Bienvenidos",
    "subtitle": "Descripción breve del sitio",
    "buttonText": "Ver más",
    "buttonLink": {
      "route_id": 1,
      "btn_label": null,
      "external_url": null,
      "file": null,
      "download_name": null,
      "anchor": null,
      "new_window": false
    }
  }
}
```

## Reglas de renderizado

- Si `title` está vacío, no se renderiza nada.
- El botón solo se muestra si `buttonText` y `buttonLink` están presentes.
