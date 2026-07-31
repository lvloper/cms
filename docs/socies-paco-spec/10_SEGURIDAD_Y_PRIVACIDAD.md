# Seguridad y privacidad

## 1. Capas de protección

```text
Cloudflare WAF / rate limiting
→ CORS y allowlist de origen
→ token de conversación
→ rate limiter de Laravel
→ validación de acción y tamaño
→ control de estado e idempotencia
→ guardrails de IA
→ validación de salida
→ persistencia auditada
```

Ninguna capa reemplaza a las demás.

## 2. Rate limiting

Aplicar límites por combinación de:

- IP o hash derivado;
- conversación;
- token;
- origen;
- ventana temporal;
- costo acumulado o cantidad de turnos.

Límites iniciales orientativos:

```text
crear conversación: 5 por 10 minutos por IP
acciones: 20 por 10 minutos por conversación/IP
concurrencia: 1 generación activa por conversación
interacciones funcionales: máximo 10 por conversación
```

Ajustar según tráfico real y plan de Cloudflare.

Cloudflare puede limitar solicitudes en el edge y Laravel vuelve a limitar usando caché. No confiar exclusivamente en IP: redes corporativas y móviles comparten direcciones.

## 3. Turnstile

No necesariamente mostrarlo al inicio. Usarlo:

- de forma invisible o administrada;
- al detectar riesgo;
- antes de crear múltiples conversaciones;
- antes de enviar contacto en escenarios sospechosos;
- después de un threshold de uso.

La validación debe hacerse en el servidor mediante Siteverify. Un token de cliente sin validación de servidor no protege el endpoint. Los tokens son de vida corta y un solo uso.

## 4. Token de conversación

- Token opaco y firmado o aleatorio de alta entropía.
- Guardar hash, no token plano.
- Scope a una conversación.
- Rotación opcional al capturar contacto.
- Expiración configurable.
- No incluir PII dentro del token.

## 5. CORS y sitios permitidos

El widget puede estar embebido en varios sitios, pero el backend debe conocer:

- orígenes permitidos;
- campañas habilitadas por origen;
- credenciales o site key pública del widget;
- límites por cliente/sitio.

No reflejar `Origin` libremente.

## 6. Validación de entrada

- límite de caracteres por mensaje;
- límite de arrays y opciones;
- MIME y dominio de video/imagen autorizados;
- normalización Unicode;
- rechazo de payloads desconocidos;
- sanitización de Markdown;
- links generados solo desde URLs aprobadas;
- teléfono normalizado;
- email validado;
- fechas y rangos validados en servidor.

## 7. Prompt injection

- El modelo no ejecuta SQL.
- No tiene shell, HTTP abierto ni acceso a secretos.
- Las tools aceptan enums, filtros y límites cerrados.
- El contenido recuperado se delimita como datos.
- La respuesta no puede crear nuevas tools ni componentes.
- Los claims se validan después del modelo.
- No mostrar prompts, razonamiento interno ni configuración.

## 8. Privacidad y PII

PII esperada:

```text
nombre
email
teléfono
organización
cargo
contenido de la consulta
```

Medidas:

- cifrado en tránsito;
- acceso interno por rol;
- logs redactados;
- no enviar PII a analytics de terceros;
- política de retención;
- posibilidad de eliminar o anonimizar;
- consentimiento o aviso breve antes del envío de contacto;
- registrar base y versión del aviso mostrado.

### Precarga de campañas

- No incluir nombre, email, teléfono o consulta en texto plano dentro de URLs de producción.
- Usar `prefill_token` opaco con payload en caché, TTL corto y origen/campaña asociados.
- No registrar el token en analytics, logs generales ni `Referer`.
- Consumir o invalidar el token al confirmar la precarga.
- Eliminar el parámetro visible de la URL después de resolverlo.
- Tratar todos los valores precargados como no confirmados hasta una acción explícita del visitante.

## 9. IP y geolocalización

El país puede obtenerse desde Cloudflare. Guardar solo lo necesario.

- Evitar almacenar IP plana por defecto.
- Usar hash con salt rotativo para abuso.
- No presentar la ubicación inferida al usuario como un dato confirmado.
- Permitir corrección si el país afecta el flujo.

## 10. Proveedor de IA

- Las credenciales permanecen en backend.
- Revisar política de retención y controles de datos del proveedor elegido.
- Desactivar almacenamiento del proveedor cuando corresponda y sea compatible.
- No enviar campos internos innecesarios.
- Redactar PII en evaluaciones y entornos de prueba.
- Mantener contrato para cambiar de proveedor.

## 11. Contenido y XSS

- Markdown con subset estricto.
- Sanitización antes de renderizar.
- Prohibido `dangerouslySetInnerHTML` con respuesta del modelo.
- Cards construidas desde datos tipados.
- Media solo desde hosts permitidos o proxy controlado.
- CSP compatible con widget y Turnstile.

## 12. Abuso y cierre

Bloquear o cerrar cuando:

- excede límite;
- automatiza mensajes repetitivos;
- intenta extraer prompts o secretos reiteradamente;
- envía payloads inválidos en serie;
- usa la conversación para contenido no relacionado de forma persistente.

La respuesta visible debe ser breve y no explicar reglas de detección.
