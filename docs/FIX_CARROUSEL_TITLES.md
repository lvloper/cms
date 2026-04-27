# Comando para Actualizar Títulos de Bloques Carrousel

## Descripción

Este comando busca todos los bloques de tipo "Carrousel" en las páginas y actualiza su título a "Te puede interesar".

## Uso

### Ver qué cambiaría sin aplicar cambios (Dry Run)

```bash
php artisan fix:carrousel-titles --dry-run
```

### Aplicar cambios

```bash
php artisan fix:carrousel-titles
```

## ¿Cuándo usar este comando?

- Cuando se necesita estandarizar el título de todos los bloques Carrousel
- Después de cambios en el diseño que requieren un título específico
- Para mantener consistencia en toda la aplicación

## Ejemplo de Salida

### En modo dry-run:

```
🔍 Buscando bloques Carrousel en Páginas...

⚠️  Modo DRY-RUN: No se realizarán cambios

  📝 Página: Prevención
     Bloque #11 (Carrousel)
     • "Novedades" → "Te puede interesar"
     🌐 http://127.0.0.1:8000/prevencion

  📝 Página: Derechos
     Bloque #5 (Carrousel)
     • "Novedades" → "Te puede interesar"
     🌐 http://127.0.0.1:8000/derechos

📊 Se encontraron 3 bloques Carrousel en 3 páginas que necesitan actualización.
💡 Ejecuta sin --dry-run para aplicar los cambios.
```

### Al aplicar cambios:

```
🔍 Buscando bloques Carrousel en Páginas...

  ✓ Página: Prevención
     Bloque #11 (Carrousel)
     • "Novedades" → "Te puede interesar"
     🌐 http://127.0.0.1:8000/prevencion

✅ Se actualizaron 3 bloques Carrousel en 3 páginas.
```

## ¿Qué Hace el Comando?

1. Busca todas las páginas en la base de datos
2. Itera sobre los bloques de cada página
3. Identifica bloques de tipo "Carrousel"
4. Verifica si el título actual es diferente de "Te puede interesar"
5. Actualiza el título si es necesario
6. Guarda los cambios en la base de datos

## Estructura del Bloque

El comando busca bloques con esta estructura:

```php
[
    'type' => 'Carrousel',
    'data' => [
        'title' => 'Título actual', // Se cambiará a "Te puede interesar"
        // ... otros campos
    ]
]
```

## Notas Técnicas

- Solo busca en el modelo `Page` (no en `Blog` ni `Jobsoffer`)
- Solo actualiza bloques que tengan un título diferente a "Te puede interesar"
- Es seguro ejecutar el comando múltiples veces (no duplica cambios)
- Los cambios se guardan directamente en la base de datos

## Casos de Uso

### 1. Auditoría inicial
Ver cuántos bloques necesitan actualización:
```bash
php artisan fix:carrousel-titles --dry-run
```

### 2. Aplicar cambios
```bash
php artisan fix:carrousel-titles
```

### 3. Verificar que se aplicaron correctamente
```bash
php artisan fix:carrousel-titles --dry-run
```
Si no hay cambios pendientes, mostrará: "✅ No se encontraron bloques Carrousel que necesiten actualización."

## Prevención

Para evitar que los bloques Carrousel tengan títulos incorrectos en el futuro, el archivo `CarrouselBlock.php` ya tiene configurado el valor por defecto:

```php
FormShortcuts::Input(name: 'title')
    ->label('Titulo Principal')
    ->default('Te puede interesar')
```

Esto asegura que todos los nuevos bloques Carrousel tendrán automáticamente "Te puede interesar" como título.

## Integración con Otros Comandos

Este comando puede ejecutarse como parte de un proceso de actualización junto con otros comandos de mantenimiento:

```bash
# Actualizar títulos de Carrousel
php artisan fix:carrousel-titles

# Capitalizar tags
php artisan fix:blog-tags

# Limpiar tags duplicados
php artisan clean:duplicate-tags

# Verificar enlaces externos
php artisan check:external-links
```

## Ayuda

```bash
php artisan fix:carrousel-titles --help
```
