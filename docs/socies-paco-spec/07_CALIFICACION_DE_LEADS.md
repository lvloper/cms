# Calificación de leads

## 1. Objetivo

Calificar no significa completar un formulario largo. Significa obtener suficiente información para decidir:

- si Socies tiene encaje;
- qué tipo de consulta es;
- cuál sería la próxima acción interna;
- qué prioridad merece;
- qué información falta antes de contactar.

## 2. Datos mínimos

Un lead contactable necesita:

```text
nombre
email o WhatsApp
motivo de consulta
bajada o descripción útil
```

Todo lo demás es dinámico.

## 3. Datos por importancia

### Alta

- intención o servicio probable;
- problema u objetivo;
- organización;
- cargo;
- rol en la decisión;
- escala relevante;
- modalidad: puntual, mensual, mantenimiento, consultoría o pack.

### Media

- cantidad de empleados;
- facturación;
- urgencia;
- fecha límite;
- herramientas existentes;
- cantidad de usuarios;
- frecuencia o volumen.

### Contextual

- presupuesto declarado espontáneamente;
- tecnologías preferidas;
- país obtenido por IP;
- página o campaña de origen;
- contenido visitado.

## 4. Pregunta sobre decisión

Pregunta recomendada:

> ¿Qué rol tenés en esta decisión?

Opciones:

```text
La decisión depende de mí
La evaluamos en equipo
Estoy relevando opciones
Por ahora solo estoy averiguando
Prefiero no responder
```

Mapeo interno sugerido:

```text
decision_maker
shared_decision
researcher
early_research
unknown
```

No usar “¿Sos quien decide?”.

## 5. Cantidad de empleados

Rangos iniciales editables:

```text
1
2–5
6–15
16–50
51–200
Más de 200
Prefiero no responder
```

El playbook puede reemplazar “empleados” por usuarios, sedes, campañas, operaciones mensuales u otra medida de escala más relevante.

## 6. Facturación

No preguntar en todos los flujos. Mostrar una explicación breve.

> Esto nos ayuda a entender la escala de la solución. ¿En qué rango de facturación anual se encuentran?

Rangos iniciales para empresas pequeñas y organizaciones argentinas:

```text
Hasta USD 25.000 al año
USD 25.000–75.000
USD 75.000–250.000
USD 250.000–1 millón
Más de USD 1 millón
Prefiero no responder
```

Los rangos deben ser configurables por campaña, país, rubro y tipo de servicio. Para algunos casos puede ser más útil preguntar presupuesto anual del área, cantidad de beneficiarios o volumen operativo.

## 7. Presupuesto

- No preguntar en todos los flujos.
- En sistemas a medida puede preguntarse si existe un rango previsto, siempre como dato opcional y permitiendo “todavía no lo definimos” o “prefiero no responder”.
- Si el usuario menciona un monto, registrarlo con moneda, periodo y evidencia.
- No presentar una cifra ambigua como presupuesto de proyecto.
- No descartar automáticamente por presupuesto bajo; buscar pack o alternativa publicada.

## 8. Scoring inicial

Puntaje interno de 0 a 100.

| Dimensión | Peso máximo |
|---|---:|
| Encaje con servicios | 25 |
| Claridad y relevancia del problema | 15 |
| Escala u oportunidad | 15 |
| Rol en la decisión | 15 |
| Madurez para avanzar | 15 |
| Momento o urgencia | 10 |
| Calidad de interacción | 5 |

### Reglas

- No informar el score al visitante.
- No penalizar fuertemente la ausencia de una respuesta sensible.
- Una inferencia débil aporta como máximo una fracción del puntaje.
- El fit con un pack puede compensar una escala baja.
- El score propone prioridad; no toma la decisión final.
- Guardar explicación y versión de reglas.

## 9. Clasificaciones finales

### Tipo de consulta

```text
new_project
monthly_service
maintenance
consulting
pack
partnership
vendor
job
support_or_existing_client
general
```

### Nivel de encaje

```text
high
medium
low
insufficient_information
non_commercial
```

### Próxima acción sugerida

```text
call
written_reply
offer_pack
ask_for_more_information
manual_review
low_priority
```

### Estado operativo

Todo lead nuevo con contacto termina como:

```text
pending_review
```

El equipo cambia luego el estado a contactado, descartado, oportunidad, reunión, etc.

## 10. Suficiencia por playbook

Ejemplo landing:

```text
contacto
+ objetivo de la landing
+ estado de contenidos
+ relación del contacto con el proyecto
+ estructura: persona, equipo u organización
```

Ejemplo servicio mensual:

```text
contacto
+ tipo de necesidad recurrente
+ frecuencia/volumen
+ equipo actual o rol del contacto
```

Ejemplo automatización:

```text
contacto
+ proceso actual
+ problema principal
+ personas/herramientas involucradas
```

Ejemplo sistema a medida:

```text
contacto
+ proceso actual y problema principal
+ escala relevante
+ contexto opcional de inversión
```

## 11. Clasificación de “ONG chica”

La frase permite inferir:

```text
organization_type = ngo (explícito)
organization_size = small (explícito, relativo)
budget_capacity_estimate = low (inferencia débil)
```

Uso permitido:

- priorizar casos de ONG;
- evitar ejemplos desproporcionados;
- considerar packs simples;
- preguntar por objetivo, contenidos y rol antes de preguntas financieras.

Uso no permitido:

- decir que tiene poco presupuesto;
- rechazarla;
- bajar el score a cero;
- asumir que no puede contratar.
