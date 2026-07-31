# Modelo de datos

## 1. Criterio

Usar columnas normales para datos centrales y consultados frecuentemente. Usar `jsonb` para estado flexible, evidencia, snapshots y payloads versionados.

No guardar toda la lógica únicamente en un JSON de conversación.

## 2. Tablas principales

### `conversations`

```text
id uuid pk
public_token_hash
campaign_id nullable
status
stage
locale
origin_url
origin_host
referrer
utm_source
utm_medium
utm_campaign
client_ip_hash nullable
country_code nullable
user_agent_summary nullable
interaction_count default 0
useful_interaction_count default 0
version default 1
last_activity_at
closed_at nullable
created_at
updated_at
```

### `conversation_events`

Registro append-only.

```text
id uuid pk
conversation_id
sequence
actor: user|assistant|system|tool
kind: text|component_submit|assistant_turn|state_change|security|error
payload jsonb
idempotency_key nullable
model_run_id nullable
created_at
```

Índices:

```text
unique(conversation_id, sequence)
unique(conversation_id, idempotency_key) where idempotency_key is not null
```

### `leads`

```text
id uuid pk
conversation_id unique
status default pending_review
name nullable
organization_name nullable
role_title nullable
contact_channel nullable
email nullable
phone_e164 nullable
primary_intent_code nullable
consultation_type nullable
fit_level nullable
score nullable
score_confidence nullable
next_action nullable
summary nullable
problem_summary nullable
country_code nullable
employees_range nullable
revenue_range nullable
decision_role nullable
urgency nullable
deadline nullable
budget_mentioned_amount nullable
budget_mentioned_currency nullable
state jsonb
qualified_at nullable
created_at
updated_at
```

### `lead_attributes`

Permite evidencia y datos variables.

```text
id
lead_id
field_code
value_json
value_text nullable
evidence_type: explicit|inferred|system|campaign
evidence_text nullable
confidence numeric
source_event_id nullable
is_current boolean
surface_to_user boolean
created_at
superseded_at nullable
```

No sobrescribir evidencia histórica; marcar el registro anterior como no vigente.

### `lead_scores`

```text
id
lead_id
score_total
fit_score
clarity_score
scale_score
decision_score
readiness_score
timing_score
interaction_score
rules_version
explanation jsonb
created_at
```

## 3. Configuración comercial

### `campaigns`

```text
id
code unique
name
status
initial_message
context jsonb
preferred_playbook_id nullable
max_interactions nullable
starts_at nullable
ends_at nullable
```

La precarga de PII no se guarda en `context`. Se crea un registro temporal en caché asociado a un token opaco, con TTL, origen y campaña.

### `intents`

```text
id
code unique
name
description
status
```

### `playbooks`

```text
id
code unique
name
objective
status
max_interactions
max_questions_after_contact
minimum_sufficiency_score
settings jsonb
version
```

### `playbook_intents`

Relación N:N con prioridad.

### `playbook_fields`

```text
playbook_id
field_code
importance: required|high|medium|low
ask_condition jsonb
question_id nullable
priority
```

### `questions`

```text
id
code unique
field_code
prompt
short_prompt nullable
component_type
options jsonb nullable
is_sensitive
is_skippable
validation_schema jsonb
status
version
```

### `response_blocks`

```text
id
code unique
block_type
intent_id nullable
stage nullable
campaign_id nullable
text
allowed_variables jsonb
adaptation_mode
status
priority
version
```

### `service_fit_rules`

```text
id
code unique
intent_id nullable
status: supported|conditional|unsupported|unknown
conditions jsonb nullable
approved_response_block_id nullable
alternative_service_ids jsonb nullable
priority
version
active
created_at
updated_at
```

La aplicación resuelve estas reglas antes de pedir al compositor que redacte una respuesta.

## 4. Conocimiento

### `knowledge_entities`

Tabla opcional de proyección común para facilitar búsqueda cruzada.

```text
id
entity_type
entity_id
title
summary
chat_text
url nullable
image_url nullable
chat_enabled
published
locale
metadata jsonb
updated_at
```

Las entidades reales permanecen en sus tablas de dominio.

### `knowledge_chunks`

Definida en `03_CMS_Y_BASE_DE_CONOCIMIENTO.md`.

### `content_impressions`

```text
id
conversation_id
event_id
entity_type
entity_id
presentation_type
rank
reason_code
created_at
```

Permite medir qué contenido ayuda a convertir.

## 5. Ejecuciones de modelo

### `model_runs`

```text
id uuid pk
conversation_id
phase: analyzer|composer|embedding|summary
provider
model
prompt_version
input_hash
input_snapshot jsonb optional_or_redacted
output_snapshot jsonb
validated boolean
validation_errors jsonb nullable
latency_ms
input_tokens nullable
output_tokens nullable
cost_estimate nullable
request_id nullable
created_at
```

Definir política de redacción para no duplicar PII innecesariamente.

## 6. Seguridad y operación

### `security_events`

```text
id
conversation_id nullable
ip_hash nullable
event_type
severity
metadata jsonb
created_at
```

### `conversation_locks`

Preferir lock en Redis. Si no está disponible, usar advisory locks de PostgreSQL o bloqueo optimista por versión.

## 7. Snapshot de estado

`leads.state` puede contener:

```json
{
  "explicit": {},
  "inferred": {},
  "missing_fields": [],
  "content_shown": [],
  "last_objective": "identify_role",
  "off_topic_count": 0,
  "non_useful_reply_count": 0,
  "contact_captured": true,
  "sufficiency": 0.78
}
```

Este snapshot acelera la ejecución, pero puede reconstruirse desde eventos y atributos.

## 8. Retención y cierre

- Conversación con contacto: se conserva según política comercial y privacidad.
- Conversación anónima abandonada: retención corta y configurable.
- IP: guardar hash con salt rotativo, no IP plana salvo necesidad justificada.
- Eliminar o anonimizar por solicitud sin romper métricas agregadas.
