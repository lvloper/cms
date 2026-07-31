# Seeds iniciales de Paco

**Estado:** contrato obligatorio para la implementación  
**Objetivo:** permitir que el proyecto funcione después de migrar y ejecutar seeds, sin configuración manual previa.

## 1. Principio

La instalación inicial debe poder ejecutar:

```bash
php artisan migrate --seed
```

y obtener una conversación funcional con:

- taxonomía de intenciones;
- playbooks mínimos;
- preguntas reutilizables;
- bloques de respuesta seguros;
- campañas básicas;
- reglas iniciales de encaje;
- un flujo genérico completo.

Los seeds no crean clientes, trabajos, métricas o testimonios ficticios en producción.

## 2. Organización de seeders

```text
DatabaseSeeder
└── PacoBootstrapSeeder
    ├── PacoIntentSeeder
    ├── PacoQuestionSeeder
    ├── PacoResponseBlockSeeder
    ├── PacoPlaybookSeeder
    ├── PacoServiceFitRuleSeeder
    └── PacoCampaignSeeder

PacoDemoSeeder                 solo local/testing
├── PacoDemoKnowledgeSeeder
└── PacoDemoConversationSeeder

ClientWorksDemoSeeder          solo local/testing
```

Orden obligatorio:

1. intenciones;
2. preguntas;
3. bloques de respuesta;
4. playbooks y sus relaciones;
5. reglas de encaje;
6. campañas.

## 3. Idempotencia y edición desde CMS

Todos los registros base usan un `code` estable.

El bootstrap:

- usa `firstOrCreate` para contenido editorial editable;
- completa relaciones faltantes sin borrar relaciones agregadas desde CMS;
- nunca elimina registros;
- nunca sobrescribe textos modificados por el equipo;
- puede ejecutarse más de una vez sin duplicar datos;
- falla de forma visible si un código existente tiene un tipo incompatible.

Una modificación futura del contenido base se realiza mediante un seeder de actualización explícito o una migración de datos, no cambiando silenciosamente el bootstrap histórico.

El `PacoDemoSeeder` puede reconstruir sus propios datos, pero solo se ejecuta en entornos `local` o `testing`.

## 3.1 Trabajos demo de clientes

Para probar las páginas de clientes y preparar evidencia para la futura búsqueda de Paco sin esperar a tener casos reales:

```bash
php artisan db:seed --class=Database\\Seeders\\ClientWorksDemoSeeder
```

`ClientWorksDemoSeeder` toma los clientes existentes y carga dos trabajos ficticios en el campo `Cliente > Trabajos`, usando exactamente el schema del repeater (`title`, `categories`, `external_url`, `image`, `description`).

El seeder:

- solo se puede ejecutar fuera de producción;
- es idempotente;
- no toca testimonios;
- no reemplaza trabajos que ya existan;
- deja los títulos marcados como `Demo —` para no confundirlos con casos reales.

Estos trabajos aparecen en la página pública del cliente, pero no se habilitan como evidencia comercial: al no tener `use_authorized` y `chat_enabled`, Paco los excluye. La conversación sí recupera y muestra automáticamente trabajos y testimonios que tengan autorización explícita en un cliente publicado y también autorizado.

## 4. Intenciones iniciales

| Código | Nombre | Tipo |
|---|---|---|
| `landing_page` | Landing o campaña | Comercial |
| `web_institucional` | Sitio institucional | Comercial |
| `plataforma_a_medida` | Sistema o plataforma a medida | Comercial |
| `automatizacion` | Automatización de procesos | Comercial |
| `integracion` | Integración entre sistemas | Comercial |
| `mantenimiento` | Evolución o mantenimiento | Comercial |
| `servicio_mensual` | Capacidad técnica recurrente | Comercial |
| `consultoria` | Consulta técnica o de producto | Comercial |
| `partnership` | Agencia, consultora o socio comercial | Comercial |
| `pack` | Pack publicado | Comercial |
| `support_existing_client` | Cliente actual que necesita soporte | Derivación |
| `job` | Búsqueda laboral | No comercial |
| `vendor` | Proveedor que ofrece servicios | No comercial |
| `general` | Consulta todavía no clasificada | Fallback |

Los códigos son internos y no se muestran directamente al visitante.

## 5. Preguntas iniciales

### Comunes

| Código | Campo | Componente | Obligatoria |
|---|---|---|---:|
| `initial_need` | `problem_summary` | `text_input` | Sí |
| `contact` | datos de contacto | `contact_form` | Sí |
| `organization_name` | `organization_name` | `text_input` | No |
| `decision_role` | `decision_role` | `single_select` | No |
| `target_date` | `deadline` | `date` | No |
| `relevant_scale` | `relevant_scale` | `single_select` | No |

### Landing y sitio

| Código | Campo | Componente |
|---|---|---|
| `landing_goal` | `landing_goal` | `single_select` |
| `content_readiness` | `content_readiness` | `single_select` |
| `design_readiness` | `design_readiness` | `single_select` |
| `maintenance_need` | `maintenance_need` | `single_select` |
| `contact_context` | `contact_context` | `single_select` con detalle opcional |
| `organization_structure` | `organization_structure` | `single_select` con detalle opcional |
| `budget_context` | `budget_context` | `single_select` sensible y omitible |

Las opciones `partial` y `need_help` de `content_readiness` habilitan una aclaración escrita opcional. El CMS permite activar esta aclaración en cualquier opción, definir su texto, placeholder, obligatoriedad y longitud máxima.
Todas las opciones de `landing_goal` permiten complementar manualmente la selección; `other` exige una aclaración.

### Sistemas, automatización e integración

| Código | Campo | Componente |
|---|---|---|
| `current_process` | `current_process` | `text_input` |
| `main_pain` | `main_pain` | `text_input` |
| `tools_involved` | `tools_involved` | `text_input` |
| `people_or_volume` | `relevant_scale` | `text_input` |

### Servicio recurrente y mantenimiento

| Código | Campo | Componente |
|---|---|---|
| `current_platform` | `current_platform` | `text_input` |
| `recurrence_frequency` | `recurrence_frequency` | `single_select` |
| `internal_team` | `internal_team` | `single_select` |
| `work_type` | `work_type` | `multi_select` |

Las opciones concretas se guardan como datos editoriales versionados. Todas las preguntas sensibles permiten “Prefiero no responder”.

## 6. Bloques de respuesta iniciales

| Código | Tipo | Función |
|---|---|---|
| `acknowledgement_default` | `acknowledgement` | Confirmar brevemente lo entendido |
| `contact_transition_default` | `contact_transition` | Pedir contacto sin cortar el hilo |
| `experience_intro_default` | `experience_intro` | Introducir evidencia real del CMS |
| `no_evidence_default` | `experience_intro` | Continuar sin fingir un caso comparable |
| `qualification_transition_default` | `qualification_transition` | Explicar por qué se pregunta un dato |
| `price_policy_default` | `price_policy` | Responder cuando no existe precio autorizado |
| `time_policy_default` | `time_policy` | Responder cuando no existe plazo autorizado |
| `unsupported_default` | `unsupported` | Cerrar una necesidad explícitamente no ofrecida |
| `unknown_fit_default` | `clarification` | No inventar encaje cuando falta regla editorial |
| `off_topic_first` | `off_topic` | Redirigir una vez |
| `off_topic_close` | `closing` | Cerrar reincidencia fuera de tema |
| `closing_sufficient` | `closing` | Confirmar que el equipo se pondrá en contacto |
| `closing_low_information` | `closing` | Cierre cordial sin información suficiente |
| `technical_error` | `error` | Fallback recuperable |
| `rate_limited` | `error` | Límite de uso sin revelar reglas internas |

Los textos exactos se toman de `06_PROMPTS_Y_GUARDRAILS.md` y se pueden editar desde CMS después del bootstrap.

## 7. Playbooks iniciales

### `general_discovery`

Fallback para una consulta comercial todavía no clasificada.

```text
required: initial_need + contact
high: organization_name
max_interactions: 7
max_questions_after_contact: 2
```

### `landing_project`

```text
intents: landing_page, web_institucional
required: initial_need + contact
high: landing_goal, content_readiness
conditional: design_readiness, target_date, decision_role, maintenance_need
max_interactions: 7
max_questions_after_contact: 2
```

### `custom_system`

```text
intents: plataforma_a_medida
required: initial_need + contact
high: current_process, main_pain
conditional: tools_involved, people_or_volume, decision_role, target_date
max_interactions: 8
max_questions_after_contact: 3
```

### `automation_integration`

```text
intents: automatizacion, integracion
required: initial_need + contact
high: current_process, main_pain, tools_involved
conditional: people_or_volume, decision_role
max_interactions: 8
max_questions_after_contact: 3
```

### `maintenance_monthly`

```text
intents: mantenimiento, servicio_mensual
required: initial_need + contact
high: current_platform, work_type
conditional: recurrence_frequency, internal_team, decision_role
max_interactions: 8
max_questions_after_contact: 3
```

### `partnership`

```text
intents: partnership
required: initial_need + contact
high: organization_name, work_type
conditional: recurrence_frequency, internal_team, decision_role
max_interactions: 7
max_questions_after_contact: 2
```

### `non_commercial`

```text
intents: job, vendor, support_existing_client
required: initial_need
contact: según regla editorial
max_interactions: 3
```

El playbook `pack` no se publica hasta que exista al menos un pack activo, vigente y con precio público en CMS.

## 8. Reglas iniciales de encaje

Seeds seguros:

| Intención | Estado inicial | Motivo |
|---|---|---|
| `landing_page` | `supported` | Primera vertical definida |
| `web_institucional` | `supported` | Mismo dominio funcional inicial |
| `plataforma_a_medida` | `supported` | Servicio principal declarado |
| `automatizacion` | `supported` | Servicio principal declarado |
| `integracion` | `supported` | Servicio principal declarado |
| `mantenimiento` | `supported` | Servicio principal declarado |
| `servicio_mensual` | `supported` | Capacidad técnica recurrente declarada |
| `partnership` | `supported` | Servicio para agencias y socios declarado |
| `consultoria` | `conditional` | Requiere revisar objetivo y alcance |
| `pack` | `conditional` | Solo si existe un pack publicable aplicable |
| `job` | `unsupported` | Flujo no comercial con respuesta aprobada |
| `vendor` | `unsupported` | Flujo no comercial con respuesta aprobada |
| `support_existing_client` | `conditional` | Debe derivarse al canal correspondiente |
| `general` | `unknown` | Requiere clasificación o revisión |

No se seedearán otras necesidades como `unsupported` sin aprobación editorial explícita.

## 9. Campañas iniciales

### `home_default`

```text
nombre: Home de Socies
playbook: general_discovery
mensaje: ¿En qué podemos ayudarte?
origen permitido: sitio Socies
max_interactions: 7
estado: active
```

### `direct_default`

```text
nombre: Enlace directo
playbook: general_discovery
mensaje: ¿En qué podemos ayudarte?
max_interactions: 7
estado: active
```

### `landing_services`

```text
nombre: Landing y sitios
intent: landing_page
playbook: landing_project
mensaje inicial aprobado y editable
max_interactions: 7
estado: draft
```

### `automation_services`

```text
nombre: Automatización e integraciones
intent: automatizacion
playbook: automation_integration
mensaje inicial aprobado y editable
max_interactions: 8
estado: draft
```

### `monthly_services`

```text
nombre: Evolución y capacidad mensual
intent: servicio_mensual
playbook: maintenance_monthly
mensaje inicial aprobado y editable
max_interactions: 8
estado: draft
```

Solo `home_default` y `direct_default` quedan activas al instalar. Las campañas segmentadas quedan en draft hasta revisar copy, origen y UTMs.

## 10. Demo local

`PacoDemoSeeder` puede crear:

- un trabajo ficticio claramente marcado como demo;
- un testimonio ficticio no publicable;
- una conversación completa por cada playbook;
- casos `supported`, `conditional`, `unsupported` y `unknown`;
- ejecuciones fake sin llamar a un proveedor real.

Protección obligatoria:

```php
if (! app()->environment(['local', 'testing'])) {
    throw new LogicException('PacoDemoSeeder solo puede ejecutarse en local/testing.');
}
```

## 11. Integración con `DatabaseSeeder`

```php
public function run(): void
{
    // Seeds existentes del CMS...

    $this->call(PacoBootstrapSeeder::class);

    if (app()->environment(['local', 'testing'])) {
        $this->call(PacoDemoSeeder::class);
    }
}
```

En producción puede omitirse el demo mediante configuración, pero el bootstrap es obligatorio.

## 12. Pruebas de seeders

Tests mínimos:

- el bootstrap corre dos veces sin duplicar registros;
- existen todos los códigos obligatorios;
- cada campaña referencia un playbook existente;
- cada playbook tiene preguntas válidas;
- las reglas de encaje cubren todas las intenciones base;
- solo dos campañas quedan activas inicialmente;
- el bootstrap no sobrescribe un texto editado;
- el demo falla fuera de `local` y `testing`;
- no existen clientes, trabajos o testimonios ficticios publicables después del bootstrap.

## 13. Criterio de aceptación

Después de `migrate --seed`, usando proveedor fake, debe ser posible:

1. abrir la conversación de home;
2. enviar una consulta genérica;
3. clasificarla en un playbook;
4. pedir contacto;
5. realizar una pregunta relevante;
6. cerrar como `pending_review`;
7. revisar eventos, atributos y resumen en el admin.
