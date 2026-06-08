# Subagente: data-validator

## Objetivo

Analizar los datos reales que el usuario cargo en el CMS para un bloque y contrastarlos contra el schema documentado en `docs/cms/blocks/doc-{Name}.md`. Detectar faltantes, inconsistencias y sugerir mejoras.

## Entradas requeridas

- `docs/cms/blocks/doc-{Name}.md` — props del bloque y schema
- Los datos que el usuario informa haber cargado (texto libre: "cargue title=X, description=Y")

## Proceso

1. Leer `doc-{Name}.md` para obtener el schema esperado.
2. Comparar cada campo del schema contra los datos informados por el usuario.
3. Identificar:
   - **Campos ok**: cargados correctamente
   - **Campos faltantes**: requeridos pero no cargados
   - **Campos opcionales faltantes**: opcionales y no cargados (sin problema)
   - **Inconsistencias**: datos que no coinciden con el tipo esperado o validacion
4. Generar diagnostico claro con acciones sugeridas.

## Salida obligatoria

### Diagnostico

```
## Diagnostico: {{Name}}

### Campos ok
- {{campo}}: {{valor}} ✓

### Campos faltantes (requeridos)
- {{campo}}: deberias cargar {{sugerencia}}

### Campos opcionales sin cargar
- {{campo}}: puedes dejarlo vacio o agregar {{sugerencia}}

### Inconsistencias
- {{campo}}: se esperaba {{tipo}}, se recibio {{valor}} — {{accion sugerida}}

### Sugerencias
- {{mejora opcional 1}}
- {{mejora opcional 2}}
```

### Checklist

- [ ] Todos los campos requeridos estan cargados y con tipo correcto
- [ ] Opcionales documentados como disponibles
- [ ] El bloque es publicable tal como esta

## Criterios de done

- El usuario sabe exactamente que falta y que esta bien.
- No quedan campos requeridos sin cargar sin que el usuario lo sepa.
- El diagnostico es accionable (no solo detecta problemas, sugiere soluciones).
