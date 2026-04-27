# Comando de Verificación de Enlaces Externos

## Descripción

Este comando busca en todos los contenidos (Páginas, Blogs, Ofertas Laborales) enlaces externos creados con el sistema de `FormShortcuts::RoutePicker()` que no tienen configurada la opción de "abrir en nueva pestaña".

## ¿Por qué es importante?

Los enlaces externos deben abrirse en nueva pestaña para:
- Mantener al usuario en tu sitio web
- Mejorar la experiencia de usuario (UX)
- Evitar que el usuario pierda el contenido que estaba leyendo
- Seguir las mejores prácticas de usabilidad web

## Uso

### Ver problemas sin corregir

```bash
php artisan check:external-links
```

Este comando mostrará todos los enlaces externos que no tienen `new_window` activado.

### Corregir automáticamente

```bash
php artisan check:external-links --fix
```

Este comando encontrará y corregirá automáticamente todos los enlaces externos, estableciendo `new_window = true`.

### Revisar solo un tipo de contenido

```bash
# Solo páginas
php artisan check:external-links --model=page

# Solo blogs
php artisan check:external-links --model=blog

# Solo ofertas laborales
php artisan check:external-links --model=jobsoffer
```

### Combinar opciones

```bash
# Revisar y corregir solo blogs
php artisan check:external-links --model=blog --fix
```

## Salida del Comando

El comando mostrará:

- **Registro**: El título de la página/blog/oferta
- **URL Externa**: La URL del enlace externo
- **Bloque**: El número de bloque y la ruta donde se encontró el problema
- **Ver**: Un enlace para ver el contenido en el sitio

### Ejemplo de salida:

```
🔍 Buscando enlaces externos sin nueva pestaña...

Revisando: Páginas
  
  ⚠️  Problema encontrado:
     📄 Registro: Sobre Nosotros
     🔗 URL Externa: https://example.com
     📍 Bloque #2 → data.link
     🌐 Ver: http://localhost/sobre-nosotros
  
  └─ Encontrados: 1 problemas

Revisando: Blogs
  └─ ✓ Sin problemas

Revisando: Ofertas Laborales
  └─ ✓ Sin problemas

⚠️  Total de problemas encontrados: 1
💡 Ejecuta el comando con --fix para corregir automáticamente.
```

## ¿Qué Enlaces Detecta?

El comando detecta enlaces creados con `FormShortcuts::RoutePicker()` donde:

1. `route_id` = '0' (indica enlace externo)
2. `external_url` tiene un valor
3. `new_window` es `false` o no está definido

## Modelos que Revisa

- **Page** (`App\Models\Page`)
- **Blog** (`App\Models\Blog`)
- **Jobsoffer** (`App\Models\Jobsoffer`)

Todos estos modelos usan el campo `blocks` que contiene un JSON con la estructura de bloques del contenido.

## Estructura de Datos que Busca

El comando busca esta estructura en los bloques:

```php
[
    'route_id' => '0',              // Indica enlace externo
    'external_url' => 'https://...',
    'new_window' => false,          // ⚠️ Problema!
]
```

## Casos de Uso

### 1. Auditoría inicial
Después de migrar contenido o importar datos:
```bash
php artisan check:external-links
```

### 2. Corrección masiva
Si encuentras muchos problemas:
```bash
php artisan check:external-links --fix
```

### 3. Revisión específica
Para verificar solo un tipo de contenido antes de publicar:
```bash
php artisan check:external-links --model=blog
```

### 4. Integración en CI/CD
Puedes agregar este comando a tu pipeline de deployment:
```bash
php artisan check:external-links || echo "Advertencia: hay enlaces externos sin nueva pestaña"
```

## Notas Técnicas

- El comando es recursivo y busca en estructuras anidadas de bloques
- No modifica enlaces internos (route_id diferente de '0')
- No modifica enlaces a archivos (route_id = '-1')
- La opción `--fix` guarda los cambios directamente en la base de datos
- Es seguro ejecutar el comando múltiples veces

## Prevención

Para evitar este problema en el futuro, el `FormShortcuts::RoutePicker()` ya tiene configurado por defecto `new_window = true` cuando se selecciona un enlace externo (ver línea 154 del archivo).

## Ayuda

Para ver todas las opciones disponibles:
```bash
php artisan check:external-links --help
```
