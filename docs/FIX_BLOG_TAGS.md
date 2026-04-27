# Comandos para Gestión de Tags de Novedades

## Comandos Disponibles

### 1. `fix:blog-tags` - Capitalizar Tags
### 2. `clean:duplicate-tags` - Limpiar Tags Duplicados

---

## 1. Capitalizar Tags (`fix:blog-tags`)

### Descripción

Este comando busca y corrige tags en las novedades (blogs) que están en minúscula, cambiándolos a capitalización correcta (primera letra de cada palabra en mayúscula).

## Uso

### Ver qué cambiaría sin aplicar cambios (Dry Run)

```bash
php artisan fix:blog-tags --dry-run
```

### Aplicar cambios a todos los tags

```bash
php artisan fix:blog-tags
```

### Corregir solo un tag específico

```bash
# Solo corregir el tag "ciencia"
php artisan fix:blog-tags --tag=ciencia

# Solo corregir el tag "derechos"
php artisan fix:blog-tags --tag=derechos
```

### Combinar opciones

```bash
# Ver qué cambiaría para un tag específico sin aplicar cambios
php artisan fix:blog-tags --dry-run --tag=ciencia
```

## Ejemplos de Correcciones

El comando detecta y corrige:

- `"ciencia"` → `"Ciencia"`
- `"derechos"` → `"Derechos"`
- `"covid 19"` → `"Covid 19"`
- `"test vih"` → `"Test Vih"`
- `"vacunas"` → `"Vacunas"`
- Y muchos más...

## Salida del Comando

### En modo dry-run:

```
🔍 Buscando tags en minúscula en Novedades...

⚠️  Modo DRY-RUN: No se realizarán cambios

  📝 Blog: Título del blog
     • "ciencia" → "Ciencia"
     • "derechos" → "Derechos"
     🌐 http://localhost/novedades/titulo-del-blog

📊 Se encontraron 178 tags en 113 novedades que necesitan corrección.
💡 Ejecuta sin --dry-run para aplicar los cambios.
```

### Al aplicar cambios:

```
🔍 Buscando tags en minúscula en Novedades...

  ✓ Blog: Título del blog
     • "ciencia" → "Ciencia"
     • "derechos" → "Derechos"
     🌐 http://localhost/novedades/titulo-del-blog

✅ Se corrigieron 178 tags en 113 novedades.
```

## ¿Qué Tags Detecta?

El comando detecta tags que:
- Comienzan con letra minúscula
- Son palabras alfabéticas (ignora números, símbolos especiales)

## Cómo Capitaliza

El comando usa `MB_CASE_TITLE` que capitaliza la primera letra de cada palabra:

- `"ciencia"` → `"Ciencia"`
- `"covid 19"` → `"Covid 19"`
- `"educación sexual integral"` → `"Educación Sexual Integral"`

## Casos de Uso

### 1. Auditoría inicial (sin cambiar nada)
```bash
php artisan fix:blog-tags --dry-run
```

### 2. Corregir solo tags específicos
```bash
# Primero ver qué cambiaría
php artisan fix:blog-tags --dry-run --tag=ciencia

# Luego aplicar
php artisan fix:blog-tags --tag=ciencia
```

### 3. Corrección masiva
```bash
php artisan fix:blog-tags
```

## Notas Técnicas

- El comando usa el paquete Spatie Laravel Tags
- Los tags se sincronizan usando `syncTags()` que mantiene la relación correcta
- No crea tags duplicados, si "Ciencia" ya existe, usa ese tag
- Es seguro ejecutar el comando múltiples veces
- No afecta a otros modelos, solo a `Blog` (novedades)

## Prevención

Para evitar este problema en el futuro, asegúrate de que los editores escriban los tags con la primera letra en mayúscula al crearlos.

## Ayuda

```bash
php artisan fix:blog-tags --help
```

---

## 2. Limpiar Tags Duplicados (`clean:duplicate-tags`)

### Descripción

Este comando encuentra y elimina tags duplicados (por ejemplo, "ciencia" y "Ciencia") manteniendo solo la versión capitalizada y migrando todas las referencias.

### ¿Por qué se duplican los tags?

Cuando se capitaliza un tag usando `fix:blog-tags`, se crea un nuevo tag con la capitalización correcta. Los blogs se actualizan para usar el nuevo tag, pero el tag antiguo en minúscula puede quedar huérfano en la base de datos. Este comando limpia esos tags huérfanos.

### Uso

#### Ver qué se limpiaría sin hacer cambios

```bash
php artisan clean:duplicate-tags --dry-run
```

#### Limpiar tags duplicados

```bash
php artisan clean:duplicate-tags
```

### Comportamiento

El comando:
1. Agrupa todos los tags por su versión en minúscula
2. Identifica grupos con más de una variante (duplicados)
3. Determina cuál mantener (prioriza versiones capitalizadas y con más uso)
4. Migra todas las referencias del tag duplicado al tag que se mantiene
5. Elimina el tag duplicado

### Ejemplo de Salida

```bash
🔍 Buscando tags duplicados...

⚠️  Se encontraron 103 tags duplicados:

  📝 Tag: "ciencia" → mantener "Ciencia"
     Usos: 0
     ✓ Eliminado y migrado

  📝 Tag: "derechos" → mantener "Derechos"
     Usos: 1
     ✓ Eliminado y migrado

✅ Se limpiaron 103 tags duplicados.
```

### Casos de Uso

#### 1. Después de capitalizar tags
```bash
# Primero capitaliza
php artisan fix:blog-tags

# Luego limpia duplicados
php artisan clean:duplicate-tags
```

#### 2. Limpieza de mantenimiento
```bash
# Ejecutar periódicamente para limpiar
php artisan clean:duplicate-tags
```

### Notas Técnicas

- Trabaja directamente con la tabla `taggables` (tabla pivot)
- Si un tag duplicado está en uso, migra todas sus referencias al tag que se mantiene
- No elimina tags si causaría pérdida de datos
- Es seguro ejecutar el comando múltiples veces

---

## Flujo de Trabajo Recomendado

### Corrección Completa de Tags

```bash
# 1. Ver qué se cambiaría
php artisan fix:blog-tags --dry-run

# 2. Capitalizar tags
php artisan fix:blog-tags

# 3. Ver duplicados creados
php artisan clean:duplicate-tags --dry-run

# 4. Limpiar duplicados
php artisan clean:duplicate-tags

# 5. Verificar que no quedan duplicados
php artisan clean:duplicate-tags --dry-run
```

### Corrección de Tags Específicos

```bash
# 1. Capitalizar solo "ciencia"
php artisan fix:blog-tags --tag=ciencia

# 2. Limpiar duplicados
php artisan clean:duplicate-tags
```

---

## Prevención de Problemas Futuros

Para evitar la creación de tags duplicados:

1. **Capacitar editores**: Asegúrate de que escriban los tags con capitalización correcta desde el inicio
2. **Validación en el formulario**: Considera agregar validación para capitalizar automáticamente
3. **Mantenimiento periódico**: Ejecuta `clean:duplicate-tags` regularmente como parte del mantenimiento

---

## Ayuda de Comandos

```bash
php artisan fix:blog-tags --help
php artisan clean:duplicate-tags --help
```
