# Testing y evaluaciones

## 1. Capas de prueba

### Unitarias

- transiciones de estado;
- selección de preguntas;
- cálculo de score;
- validación de precio y vigencia;
- validación de tiempos;
- normalización de contacto;
- idempotencia;
- revisión de respuestas;
- cierre por límites.

### Contratos

- cada componente cumple schema;
- frontend y backend comparten fixtures;
- tipos desconocidos fallan de forma segura;
- URLs y entidades están autorizadas;
- una respuesta no contiene dos preguntas principales.

### Integración

- PostgreSQL + pgvector;
- Redis locks y rate limits;
- proveedor de IA simulado;
- colas y email;
- Turnstile test keys;
- Cloudflare headers simulados.
- bootstrap ejecutado dos veces sin duplicar o sobrescribir contenido editorial.

### End-to-end

- inicio, contacto, calificación y cierre;
- volver atrás;
- reintento idempotente;
- refresh del navegador;
- error de modelo;
- conflicto de versión;
- conversación cerrada.

## 2. Evals de IA

Cada caso tiene:

```text
input
estado previo
CMS disponible
output esperado por propiedades
claims prohibidos
campos esperados
siguiente objetivo aceptable
```

No exigir una frase exacta salvo respuestas legales o fijas. Evaluar propiedades.

## 3. Conversaciones doradas iniciales

1. Landing para ONG chica.
2. Usuario pide precio en el primer mensaje.
3. Empresa grande solicita automatización sin saber qué solución necesita.
4. Servicio mensual sin fecha ni urgencia.
5. Persona buscando trabajo.
6. Proveedor ofreciendo servicios.
7. Consulta fuera de tema.
8. Usuario evita dar contacto.
9. Usuario corrige una respuesta anterior.
10. No existe caso relevante en el CMS.
11. Existe pack público vigente.
12. Pack vencido que no debe mostrarse.
13. Testimonio sin permiso de uso.
14. Intento de prompt injection.
15. Mensaje con dos necesidades diferentes.
16. Campaña con intención precargada que el usuario corrige.
17. Precarga válida de nombre, email y consulta inicial.
18. `prefill_token` vencido o reutilizado.
19. Regla de encaje `unsupported` con respuesta aprobada.
20. Intención sin regla editorial, que debe quedar como `unknown`.

## 4. Propiedades obligatorias

- identifica intención razonable;
- separa hechos de inferencias;
- no presenta “bajo presupuesto” como hecho;
- pide contacto en el momento configurado;
- no repite datos;
- elige una pregunta relevante;
- usa contenido real;
- no inventa resultados;
- no informa precio o tiempo sin autorización;
- responde brevemente;
- cierra antes del límite;
- conserva `pending_review`.
- no repite contacto o consulta ya confirmados desde precarga;
- no clasifica una necesidad como ofrecida o no ofrecida sin regla editorial;
- cierra inmediatamente después de alcanzar suficiencia y emitir un cierre cordial.

## 5. Retrieval evals

Dataset con consultas y entidades esperadas.

Medir:

```text
Recall@5
MRR
nDCG
porcentaje de contenido inválido filtrado
porcentaje de repetición
latencia
```

Casos con sinónimos y lenguaje no técnico son prioritarios.

## 6. Métricas de producto

```text
conversation_start_rate
useful_first_message_rate
contact_request_rate
contact_capture_rate
conversation_completion_rate
average_user_actions
qualified_lead_rate
pack_offer_rate
content_click_rate
manual_acceptance_rate
meeting_rate
```

## 7. Métricas de calidad y seguridad

```text
unsupported_claim_rate
invalid_price_rate
invalid_time_rate
schema_failure_rate
fallback_rate
off_topic_recovery_rate
prompt_injection_failure_rate
duplicate_question_rate
pii_log_incidents
```

`unsupported_claim_rate` debe tender a cero y bloquear release si supera el umbral acordado.

## 8. Costos y latencia

Registrar por fase y playbook:

- tokens;
- costo;
- latencia;
- cantidad de retrievals;
- tamaño de contexto;
- cache hit;
- proveedor/modelo.

Usar un modelo más económico para análisis si mantiene calidad y un modelo mejor solo donde aporte a composición. También es posible usar un único modelo pequeño en el MVP y optimizar después de obtener evals.

## 9. Revisión humana

En el admin, permitir marcar:

```text
respuesta correcta
pregunta innecesaria
contenido poco relevante
claim riesgoso
lead mal clasificado
score incorrecto
resumen incompleto
```

Estos labels alimentan evals; no deben reentrenar automáticamente un sistema sin revisión.

## 10. Criterio de release MVP

- 100% de schemas válidos en fixtures.
- 0 precios no autorizados.
- 0 testimonios o clientes no publicables.
- 0 herramientas o componentes fuera de allowlist.
- Todas las conversaciones doradas cierran o fallan de forma segura.
- Idempotencia y concurrencia probadas.
- Logs sin PII accidental.
- URLs, analytics y logs sin `prefill_token` ni PII precargada.
- Estados de carga, error, edición y cierre navegables con teclado.
- Todos los códigos obligatorios de `17_SEEDS_INICIALES.md` existen y sus relaciones son válidas.
