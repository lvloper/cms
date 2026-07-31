# Orquestación del agente

## 1. Decisión arquitectónica

Paco no debe ser un agente autónomo con un ciclo abierto de herramientas. Debe funcionar como un **orquestador controlado con planificación adaptativa**.

Los estados y playbooks controlan seguridad, contacto, permisos y cierre, pero no fijan una secuencia literal de preguntas. El planificador puede reordenar, omitir o incorporar preguntas activas del catálogo cuando reduzcan incertidumbre para el caso actual.

Ante expresiones como “ya te dije”, correcciones u objeciones, el analizador recibe el último motivo sustantivo como memoria de corto plazo. Nunca debe afirmar que no recuerda una conversación cuyo historial está disponible.

Motivos:

- el objetivo comercial es acotado;
- debe respetar un máximo de interacciones;
- las afirmaciones deben estar documentadas;
- la salida controla componentes de UI;
- precios, tiempos y testimonios requieren validación estricta;
- es necesario auditar por qué preguntó o mostró algo.

## 2. Pipeline por turno

```text
1. recibir mensaje o respuesta de componente
2. cargar conversación, lead, campaña y playbook
3. analizar mensaje y extraer hechos/inferencias
4. actualizar estado provisional
5. determinar objetivo del próximo turno
6. resolver encaje contra reglas editoriales del CMS
7. recuperar contenido y preguntas candidatas
8. componer respuesta estructurada
9. validar reglas, claims y schema
10. persistir resultado con lock/versionado
11. devolver turno atómico a React
```

## 3. Dos llamadas de modelo como máximo

### Llamada A — Analizador y planificador

Entrada:

- mensaje del usuario;
- resumen actual del lead;
- etapa de conversación;
- campaña;
- playbook;
- campos faltantes;
- últimas interacciones.

Salida estructurada:

- hechos extraídos;
- inferencias con confianza;
- correcciones de datos previos;
- intención principal y secundarias;
- etapa propuesta;
- próximo objetivo;
- tipos de contenido a recuperar;
- campos candidatos para preguntar;
- señal de cierre.

Esta llamada no redacta la respuesta final.

En la implementación actual, esta misma llamada devuelve además una lista ordenada de hasta seis códigos de pregunta y una confirmación breve. El backend valida ambos resultados: solo acepta códigos activos pertenecientes al playbook, respeta primero la importancia editorial (`required`, `high`, `medium`, `low`) y usa el orden del modelo únicamente dentro del nivel permitido. Si la salida es inválida, conserva el orden determinista del CMS. Las respuestas posteriores de componentes de texto pasan por un enrutador estructurado que clasifica `answer`, `question`, `correction`, `objection`, `low_information` u `off_topic`. Únicamente `answer` puede completar el campo activo; una pregunta u objeción genera una intervención breve y luego repite el componente pendiente.

### Recuperación determinística

La aplicación ejecuta consultas controladas:

- filtros relacionales por intención, rubro, campaña y visibilidad;
- búsqueda semántica para descubrir candidatos;
- carga exacta de entidades por ID;
- validación de precio, vigencia, testimonio y tiempo;
- ranking comercial configurable.

La recuperación puede entregar varios candidatos autorizados. En el turno posterior al contacto, un planificador recibe el historial saneado de la conversación y decide cuáles aportan al argumento, en qué orden y si corresponde destacar un testimonio. La aplicación vuelve a validar los IDs y redacta nombres, trabajos, resultados, citas y atribuciones desde datos literales del CMS.

### Llamada B — Compositor

Recibe exclusivamente:

- estado autorizado;
- hechos explícitos e inferencias permitidas;
- fragmentos aprobados;
- entidades recuperadas;
- pregunta elegida;
- reglas de tono;
- schema del turno.

Devuelve:

- texto breve;
- componentes permitidos;
- IDs de contenido mostrado;
- `lead_patch` final;
- motivo interno de la decisión.

Para evidencia comercial, la salida del modelo se reduce a IDs seleccionados, tipo de relación, reconocimiento contextual y testimonio a destacar. El texto factual se arma en backend para que la mayor autonomía editorial no amplíe la capacidad de inventar claims.

## 4. Cuándo evitar la segunda llamada

La aplicación puede devolver una respuesta fija sin compositor cuando existe una política exacta:

- validación de contacto;
- límite de interacciones;
- rate limit;
- consulta fuera de tema reiterada;
- cierre por falta de datos;
- error técnico;
- precio solicitado sin contexto;
- mensaje legal o de privacidad.

## 5. Estados de conversación

```text
new
understanding_need
contact_required
trust_building
qualifying
ready_to_close
closed_pending_review
closed_abandoned
blocked
```

### Reglas de transición

- `new → understanding_need`: primer mensaje con contenido válido.
- `understanding_need → contact_required`: al completar dos acciones o antes si el playbook lo indica.
- `new|understanding_need → trust_building`: solo si consulta y contacto precargados fueron confirmados y el estado ya cumple las condiciones intermedias.
- `contact_required → trust_building`: nombre y contacto válidos.
- `trust_building → qualifying`: se mostró al menos una explicación o contenido relevante, o no existe contenido aplicable.
- `qualifying → ready_to_close`: datos mínimos y umbral de suficiencia alcanzados.
- `ready_to_close → closed_pending_review`: respuesta final persistida.
- cualquier estado → `blocked`: abuso, automatización maliciosa o límite de seguridad.
- cualquier estado no cerrado → `closed_abandoned`: vencimiento o cierre explícito sin contacto.

## 6. Objetivos de turno

Lista inicial controlada:

```text
clarify_need
clarify_current_state
collect_contact
identify_organization
identify_role
identify_decision_role
measure_scale
understand_goal
understand_deadline
understand_tools
show_relevant_case
show_relevant_service
show_pack
handle_price_objection
redirect_off_topic
close_sufficient
close_low_information
```

El modelo elige entre objetivos permitidos para el estado actual. No inventa objetivos nuevos.

## 7. Tools de aplicación

El modelo nunca recibe una conexión SQL ni una herramienta genérica de consulta.

Tools recomendadas:

```text
search_knowledge(query, filters, limit)
get_entities(type, ids)
get_question_candidates(playbook_id, missing_fields)
get_response_blocks(intent, stage, campaign_id)
validate_commercial_claim(claim_type, entity_id)
calculate_lead_score(lead_state)
```

En el MVP estas tools pueden ser métodos internos invocados por el controlador, sin tool calling real del modelo.

## 8. Hechos e inferencias

Cada atributo debe conservar:

```json
{
  "field": "budget_capacity_estimate",
  "value": "low",
  "evidence_type": "inference",
  "evidence": "El usuario describió a la organización como una ONG chica",
  "confidence": 0.55,
  "source_event_id": "evt_123",
  "surface_to_user": false
}
```

Reglas:

- Un dato explícito puede completar un campo.
- Una inferencia puede ajustar ranking o preguntas.
- Una inferencia débil no puede excluir un lead.
- Una inferencia sensible no se muestra al usuario.
- Una inferencia no puede habilitar un claim comercial.
- Una respuesta posterior puede corregir o reemplazar la inferencia.

## 9. Suficiencia para cerrar

El cierre no depende de completar todas las columnas. Se calcula con reglas:

```text
contacto válido
+ motivo de consulta
+ descripción útil
+ al menos una dimensión de calificación relevante
+ próximo paso razonable
```

El playbook define qué dimensión adicional es suficiente.

La implementación calcula una cobertura explícita: motivo, contacto, todos los campos `required/high` del playbook y un mínimo configurable de preguntas posteriores al contacto. Obtener una sola respuesta de calificación ya no alcanza para cerrar.

## 10. Atomicidad y concurrencia

- Cada turno utiliza `conversation_version` u optimistic locking.
- La API recibe `idempotency_key` por acción.
- Un lock corto evita dos respuestas simultáneas para la misma conversación.
- El evento entrante se persiste en una transacción corta antes de llamar al modelo.
- El turno, los atributos derivados y el nuevo estado se persisten en una segunda transacción corta con verificación de versión.
- No mantener locks o transacciones de base de datos abiertos durante llamadas al proveedor.
- Si falla la composición, se conserva el mensaje entrante y se devuelve un fallback seguro o reintento idempotente.
