# Ejemplo completo — landing para una ONG

## Contexto

Campaña: genérica.  
Página de origen: home.  
Estado inicial: `new`.

## Turno 0 — Socies

> ¿En qué podemos ayudarte?

Componente:

```json
{
  "type":"text_input",
  "id":"initial_need",
  "required":true,
  "multiline":true
}
```

## Turno 1 — Usuario

> Necesito desarrollar una landing page para una fundación ONG chica.

## Estado interno después del análisis

```json
{
  "explicit": {
    "primary_intent": "landing_page",
    "organization_type": "ngo",
    "organization_size": "small"
  },
  "inferred": {
    "project_mode": {
      "value":"one_off_project",
      "confidence":0.75
    },
    "budget_capacity_estimate": {
      "value":"low",
      "confidence":0.55,
      "surface_to_user":false
    }
  },
  "missing_high_value_fields": [
    "landing_goal",
    "content_readiness",
    "contact"
  ]
}
```

## Turno 1 — Socies

> Entendimos: buscan una landing para la fundación. ¿Ya tienen definidos los contenidos y el objetivo principal?

```json
{
  "type":"single_select",
  "id":"content_readiness",
  "required":true,
  "options":[
    {"value":"ready","label":"Sí, ya está definido"},
    {"value":"partial","label":"Tenemos una parte"},
    {"value":"need_help","label":"Necesitamos ayuda con eso"}
  ]
}
```

## Turno 2 — Usuario

> Tenemos textos e imágenes, pero necesitamos ordenar el mensaje. La landing es para una campaña de donaciones.

## Estado actualizado

```json
{
  "landing_goal":"donations_campaign",
  "content_readiness":"partial",
  "needs":["content_structure","design","development"],
  "campaign_or_event":true
}
```

## Turno 2 — Socies: captura

> Ya tenemos un buen punto de partida. Para continuar y poder responderte, compartinos tus datos de contacto.

```json
{
  "type":"contact_form",
  "id":"contact",
  "required":true,
  "fields":[
    {"name":"name","type":"text","label":"Tu nombre","required":true},
    {
      "name":"channel",
      "type":"single_select",
      "label":"¿Cómo preferís que te contactemos?",
      "required":true,
      "options":[
        {"value":"email","label":"Email"},
        {"value":"whatsapp","label":"WhatsApp"}
      ]
    },
    {"name":"contact_value","type":"dynamic_contact","required":true}
  ]
}
```

## Turno 3 — Usuario

```json
{
  "name":"Ana",
  "channel":"whatsapp",
  "contact_value":"+54..."
}
```

## Retrieval

La aplicación busca para Socies:

```text
trabajos: landing + campaña + ONG/fundación
servicios: diseño/desarrollo de landing
packs: landing simple aplicable a ONG
casos: permiso público y chat_enabled
```

Supongamos que encuentra un trabajo real autorizado de Fundación Huésped y un testimonio relacionado.

## Turno 3 — Socies

> Ana, con Fundación Huésped desarrollamos una experiencia digital para una campaña específica, combinando estrategia de contenidos, diseño y desarrollo. Este caso puede darte una referencia de cómo abordamos proyectos comparables. ¿Qué rol tenés en esta decisión?

```json
{
  "type":"content_carousel",
  "items":[
    {"entity_type":"work","entity_id":42,"presentation":"compact"}
  ]
}
```

Pregunta en el mismo turno, después del contenido:

> ¿Qué rol tenés en esta decisión?

```json
{
  "type":"single_select",
  "id":"decision_role",
  "required":false,
  "allow_skip":true,
  "options":[
    {"value":"decision_maker","label":"La decisión depende de mí"},
    {"value":"shared_decision","label":"La evaluamos en equipo"},
    {"value":"researcher","label":"Estoy relevando opciones"},
    {"value":"early_research","label":"Por ahora solo estoy averiguando"},
    {"value":"skip","label":"Prefiero no responder"}
  ]
}
```

## Turno 4 — Usuario

> La evaluamos en equipo. Yo coordino comunicación.

Estado:

```json
{
  "role_title":"coordinación de comunicación",
  "decision_role":"shared_decision"
}
```

## Próxima pregunta elegida

Como hay una campaña, la fecha cambia alcance y prioridad. Es más útil que preguntar facturación.

## Turno 4 — Socies

> Bien. ¿La campaña tiene una fecha prevista de lanzamiento?

```json
{
  "type":"date",
  "id":"target_launch_date",
  "required":false,
  "allow_skip":true
}
```

## Turno 5 — Usuario

> Queremos lanzarla a mediados de octubre.

El backend normaliza como rango o fecha aproximada, no inventa un día exacto.

## Cierre

> Gracias, Ana. Ya tenemos un panorama claro de la campaña, los contenidos y el rol del equipo. Vamos a revisar el caso y te contactaremos por WhatsApp.

Estado final:

```json
{
  "status":"pending_review",
  "consultation_type":"new_project",
  "fit_level":"medium",
  "next_action":"manual_review",
  "score":68,
  "score_confidence":0.74
}
```

## Resumen comercial interno

```json
{
  "name":"Ana",
  "contact_channel":"whatsapp",
  "organization_type":"ngo",
  "organization_size":"small",
  "role_title":"coordinación de comunicación",
  "decision_role":"shared_decision",
  "primary_intent":"landing_page",
  "problem_summary":"Necesita una landing para campaña de donaciones; tiene textos e imágenes pero requiere ordenar el mensaje, diseñar y desarrollar.",
  "target_launch_window":"mediados de octubre",
  "content_readiness":"partial",
  "recommended_services":["landing_strategy","web_design","web_development"],
  "content_shown":[{"type":"work","id":42}],
  "budget_capacity_estimate":{
    "value":"low",
    "confidence":0.55,
    "source":"inference",
    "do_not_surface":true
  },
  "status":"pending_review"
}
```

## Qué no debe ocurrir

- “Como son una ONG chica, probablemente tienen poco presupuesto.”
- “Una landing cuesta X.”
- “Podemos tenerla lista para octubre.”
- “Este trabajo aumentó donaciones” si no existe una métrica documentada.
- Preguntar facturación solo porque la organización es chica.
- Hacer cinco preguntas más aunque ya existe información suficiente.
