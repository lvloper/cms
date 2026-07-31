# Backend e IA

## 1. Stack propuesto

- Laravel sobre una versión soportada por el proyecto.
- Paquete first-party `laravel/ai` si la versión y proveedores necesarios son compatibles.
- PostgreSQL.
- `pgvector` para recuperación semántica.
- Redis para caché, rate limits, locks y colas.
- Jobs para embeddings, emails y resúmenes secundarios.
- Endpoint síncrono para cada turno del chat.

## 2. Capas

```text
HTTP Controller
→ Conversation Application Service
→ Policy / State Transition Service
→ Analyzer
→ Knowledge Retrieval Service
→ Question Selection Service
→ Composer
→ Turn Validator
→ Persistence
→ Notification Jobs
```

No concentrar el flujo entero en un único prompt o controller.

## 3. Servicios sugeridos

```text
ConversationService
ConversationStateMachine
LeadStateService
LeadScoringService
AgentAnalyzer
KnowledgeRetriever
CommercialClaimValidator
QuestionSelector
TurnComposer
TurnSchemaValidator
ConversationSecurityService
```

## 4. Proveedor de modelos

Crear una interfaz interna:

```php
interface PacoModelGateway
{
    public function analyze(AnalyzerInput $input): AnalyzerOutput;
    public function compose(ComposerInput $input): AssistantTurn;
    public function embed(array $texts): array;
}
```

Esto evita acoplar dominio y prompts a un proveedor específico. `laravel/ai` puede implementar el gateway y permitir failover o cambio de proveedor.

## 5. Structured output

Tanto analizador como compositor deben usar salida estructurada.

- Definir schema desde DTOs o JSON Schema.
- Rechazar campos extra.
- Limitar tamaños y arrays.
- Validar nuevamente en aplicación.
- Registrar output inválido.
- Usar un solo intento adicional como máximo si existe reparación de schema.
- Si falla, devolver fallback determinístico.

## 6. Recuperación híbrida

### Consultas relacionales

Usar para:

- precio;
- vigencia;
- permisos;
- relaciones explícitas;
- campaña;
- país;
- tags;
- tipo de entidad;
- contenido publicado.

### Búsqueda vectorial

Usar para:

- similitud entre necesidad y trabajos;
- sinónimos;
- lenguaje no técnico;
- descubrimiento de servicios relacionados;
- objeciones y FAQ.

### Ranking final

Ejemplo conceptual:

```text
0.45 similitud semántica
0.20 coincidencia de intención
0.10 coincidencia de rubro
0.10 prioridad editorial
0.10 afinidad de campaña
0.05 diversidad / no repetición
```

Los pesos son configurables y deben evaluarse con conversaciones reales.

## 7. Selección de preguntas

El selector recibe:

- estado;
- playbook;
- campos faltantes;
- sensibilidad;
- interacciones restantes;
- información ya inferida;
- utilidad comercial estimada.

Puntaje de pregunta conceptual:

```text
importance
× expected_information_gain
× applicability
× user_friction_penalty
× remaining_turns_factor
```

El modelo puede proponer entre preguntas autorizadas. La aplicación toma la decisión final.

## 8. Persistencia progresiva

No mantener una transacción de base de datos abierta durante llamadas de red al proveedor. Orden por acción:

1. transacción corta: validar versión/idempotencia, guardar evento del usuario y marcar generación activa;
2. confirmar la transacción;
3. analizar, recuperar y componer fuera de la transacción;
4. validar schema, claims y transición;
5. transacción corta con lock/versión: persistir atributos, snapshot, turno y score; liberar generación activa;
6. responder;
7. despachar jobs de email/analytics si corresponde.

Si el proceso falla después del paso 1, el mensaje no se pierde. Se registra el error, se libera el estado de generación y se devuelve un fallback o reintento idempotente. Un proceso de recuperación detecta generaciones abandonadas.

## 9. Notificación al equipo

Al cierre, job asíncrono:

- genera resumen comercial;
- renderiza email;
- incluye datos normalizados;
- incluye score y explicación;
- incluye enlaces al lead y conversación;
- adjunta o enlaza JSON completo;
- registra envío y errores.

El resumen principal ya debe existir en DB antes del job.

## 10. Rendimiento

Objetivos iniciales:

```text
p50 < 2.5 s
p95 < 6 s
hard timeout del modelo < 12 s
máximo 2 llamadas de modelo por turno
```

Son objetivos de diseño, no promesas. Medir con el proveedor real.

Mejoras:

- resumir contexto antiguo;
- enviar solo últimos eventos relevantes;
- cachear playbook y bloques;
- precomputar proyecciones seguras;
- batch de embeddings;
- evitar tool loops;
- limitar resultados de retrieval.

## 11. Colas

Jobs adecuados:

```text
GenerateKnowledgeEmbedding
ReindexKnowledgeEntity
SendLeadNotification
GenerateConversationSummary
ExportConversationJson
PurgeExpiredConversation
```

No poner el turno principal en una cola en el MVP; la UI necesita respuesta inmediata.

## 12. Observabilidad

Registrar:

- request ID;
- model request ID;
- latencia por fase;
- tokens y costo estimado;
- retrieval candidates y seleccionados;
- reglas que bloquearon un claim;
- score y versión;
- errores de schema;
- fallbacks;
- rate limit y Turnstile.

No registrar secretos ni PII en logs generales.
