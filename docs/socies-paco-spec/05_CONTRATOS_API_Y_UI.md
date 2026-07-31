# Contratos API y UI

## 1. Principio

El modelo nunca devuelve HTML. Devuelve un objeto validado contra un schema cerrado. El backend controla qué componentes existen y qué datos acepta cada uno.

## 2. Crear conversación

`POST /api/paco/conversations`

```json
{
  "campaign": "landing-pages-ong",
  "prefill_token": "opaque-short-lived-token",
  "origin_url": "https://socies.example/landings",
  "referrer": "https://example.com",
  "locale": "es-AR",
  "page_context": {
    "content_type": "service",
    "content_id": 12
  }
}
```

`prefill_token` es opcional. El backend lo consume desde caché, valida campaña/origen y devuelve una precarga revisable. No se aceptan nombre o email en query params de producción.

Respuesta:

```json
{
  "conversation_id": "uuid",
  "conversation_token": "opaque-signed-token",
  "version": 1,
  "prefill": {
    "name": "Ana",
    "contact_channel": "email",
    "contact_value": "ana@example.com",
    "initial_query": "Necesitamos una landing para una campaña",
    "requires_confirmation": true
  },
  "turn": {
    "id": "turn_uuid",
    "message": "¿En qué podemos ayudarte?",
    "parts": [
      {
        "type": "text_input",
        "id": "initial_need",
        "required": true,
        "placeholder": "Contanos brevemente qué necesitás"
      }
    ]
  }
}
```

Si no existe precarga, `prefill` es `null`. La UI no envía esos valores como hechos hasta que el visitante confirma o edita la acción correspondiente.

## 3. Enviar acción

`POST /api/paco/conversations/{id}/actions`

Headers:

```text
Authorization: Bearer <conversation_token>
Idempotency-Key: <uuid>
```

Body:

```json
{
  "conversation_version": 1,
  "action": {
    "type": "text_submit",
    "component_id": "initial_need",
    "value": "Necesito desarrollar una landing page para una fundación ONG chica"
  },
  "turn_context": {
    "visible_content_ids": []
  },
  "turnstile_token": null
}
```

Este endpoint es el contrato canónico para todo envío, incluyendo respuestas, confirmación de precarga, revisión y cierre explícito. No crear endpoints paralelos `/turns` o `/answers` para la misma responsabilidad.

## 4. Respuesta de turno

```json
{
  "conversation_id": "uuid",
  "version": 2,
  "status": "active",
  "stage": "understanding_need",
  "turn": {
    "id": "turn_uuid",
    "message": "Entendimos: buscan una landing para la fundación. ¿Ya tienen definidos el contenido y el objetivo principal?",
    "parts": [
      {
        "type": "single_select",
        "id": "content_readiness",
        "required": true,
        "options": [
          {"value": "ready", "label": "Sí, ya está definido"},
          {"value": "partial", "label": "Tenemos una parte"},
          {"value": "not_ready", "label": "Necesitamos ayuda con eso"}
        ]
      }
    ],
    "content": [],
    "meta": {
      "objective": "clarify_need",
      "allow_back": true
    }
  }
}
```

## 5. Componentes permitidos

### Texto

```json
{"type":"text","markdown":"Texto breve aprobado"}
```

Markdown limitado: énfasis, listas cortas y enlaces autorizados. No HTML.

### Botones

```json
{
  "type":"buttons",
  "id":"next_action",
  "required":true,
  "options":[{"value":"continue","label":"Seguir"}]
}
```

### Selección única

```json
{
  "type":"single_select",
  "id":"decision_role",
  "required":false,
  "allow_skip":true,
  "options":[]
}
```

### Selección múltiple

```json
{
  "type":"multi_select",
  "id":"landing_needs",
  "required":false,
  "min":0,
  "max":4,
  "options":[]
}
```

### Campo libre

```json
{
  "type":"text_input",
  "id":"problem_detail",
  "required":true,
  "max_length":1500,
  "multiline":true,
  "placeholder":"Contanos brevemente"
}
```

### Contacto

```json
{
  "type":"contact_form",
  "id":"contact",
  "required":true,
  "fields":[
    {"name":"name","type":"text","required":true},
    {"name":"channel","type":"single_select","required":true,"options":["email","whatsapp"]},
    {"name":"contact_value","type":"dynamic_contact","required":true}
  ]
}
```

### Rango y slider

Solo cuando el rango representa opciones editoriales definidas; no permitir valores arbitrarios para facturación si se pretende comparar leads.

### Fecha

```json
{
  "type":"date",
  "id":"deadline",
  "required":false,
  "allow_skip":true,
  "min":"2026-07-31"
}
```

### Contenido del CMS

```json
{
  "type":"content_carousel",
  "items":[
    {"entity_type":"work","entity_id":42,"presentation":"compact"}
  ]
}
```

El frontend recibe IDs y una proyección segura preparada por backend; nunca consulta campos internos del CMS.

Forma canónica:

```json
{
  "type": "knowledge_card",
  "id": "knowledge_42",
  "entity": {
    "type": "work",
    "id": 42,
    "title": "Campaña digital",
    "summary": "Proyección pública aprobada",
    "image": null,
    "url": "/trabajos/campana-digital"
  },
  "presentation": "compact"
}
```

Tipos de entidad permitidos:

```text
service
pack
client
work
testimonial
```

### Testimonio

```json
{
  "type":"testimonial",
  "id":"testimonial_8",
  "quote":"Versión breve aprobada",
  "author":"Nombre publicable",
  "role":"Cargo publicable",
  "client":"Cliente publicable"
}
```

### Imagen y video

```json
{
  "type":"image",
  "src":"https://media.socies.example/work-42.webp",
  "alt":"Descripción editorial obligatoria",
  "width":1200,
  "height":800
}
```

```json
{
  "type":"video",
  "provider":"youtube",
  "id":"approved-video-id",
  "title":"Título accesible",
  "transcript_url":null
}
```

Media solo proviene de hosts y IDs aprobados. Upload del visitante queda fuera del MVP.

## 6. Schema interno del analizador

```json
{
  "facts": [
    {
      "field":"organization_type",
      "value":"ngo",
      "confidence":0.98,
      "evidence":"fundación ONG",
      "source":"explicit"
    }
  ],
  "inferences": [
    {
      "field":"budget_capacity_estimate",
      "value":"low",
      "confidence":0.55,
      "evidence":"ONG chica",
      "surface_to_user":false
    }
  ],
  "primary_intent":"landing_page",
  "secondary_intents":[],
  "next_objective":"clarify_need",
  "candidate_fields":["content_readiness","landing_goal"],
  "retrieval_queries":[
    {"query":"landing para ONG fundación pequeña","entity_types":["work","service","pack"]}
  ],
  "should_close":false
}
```

El analizador propone intención. La aplicación resuelve el encaje contra `service_fit_rules`; el modelo no puede declarar por sí solo que Socies ofrece o no ofrece un servicio.

## 7. `lead_patch`

El modelo propone; el backend valida y aplica.

```json
{
  "attributes":[
    {
      "field":"primary_intent",
      "value":"landing_page",
      "evidence_type":"explicit",
      "confidence":0.99,
      "source_event_id":"evt_uuid"
    }
  ]
}
```

Campos desconocidos son rechazados. El modelo no modifica `status`, `score` ni flags de seguridad directamente.

## 8. Errores

### Conflicto de versión

`409 conversation_version_conflict`

El cliente debe recargar el estado y evitar duplicar la acción.

### Validación

`422 invalid_action`

### Rate limit

`429 rate_limited`

### Conversación cerrada

`409 conversation_closed`

### Fallback seguro

Si el modelo falla, el backend puede responder:

> Tuvimos un problema para procesar eso. Probemos con una descripción breve de lo que necesitan.

El evento de error queda registrado sin perder el mensaje del usuario.

## 9. Revisión de respuestas

El backend valida:

- schema;
- longitud;
- componentes permitidos;
- opciones existentes;
- URLs permitidas;
- entidades publicables;
- precios y vigencia;
- tiempos documentados;
- claims prohibidos;
- una sola pregunta principal.

## 10. Recuperar conversación

`GET /api/paco/conversations/{id}`

Headers:

```text
Authorization: Bearer <conversation_token>
```

Devuelve versión, estado, turnos visibles y acciones disponibles. No devuelve score, inferencias ocultas, prompts ni PII sin necesidad de renderizado.

## 11. Confirmar o editar precarga

La precarga usa el endpoint canónico de acciones:

```json
{
  "conversation_version": 1,
  "action": {
    "type": "confirm_prefill",
    "values": {
      "name": "Ana",
      "contact_channel": "email",
      "contact_value": "ana@example.com",
      "initial_query": "Necesitamos una landing para una campaña"
    }
  }
}
```

El backend vuelve a validar todos los valores, consume la precarga y registra eventos normales con evidencia `campaign` o `explicit` según corresponda.

## 12. Revisar una respuesta

```json
{
  "conversation_version": 4,
  "action": {
    "type":"revise_answer",
    "target_event_id":"evt_uuid",
    "new_value":"valor actualizado"
  }
}
```

La revisión es append-only y puede invalidar inferencias, score y próximos objetivos derivados.
