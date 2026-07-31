# Decisiones abiertas

Este archivo contiene únicamente decisiones que todavía requieren una elección o validación. Las decisiones arquitectónicas cerradas viven en `adrs/`.

## 1. Proveedor y modelos

Elegir mediante spike:

- modelo de análisis;
- modelo de composición;
- modelo de embeddings;
- proveedor principal y política de fallback.

Criterios: structured output, calidad en español, latencia desde Argentina, costo y tratamiento de datos.

## 2. Retención

Definir plazos legales y comerciales para:

- conversaciones anónimas abandonadas;
- leads con contacto;
- ejecuciones de modelo;
- eventos de seguridad;
- precargas de campaña no consumidas.

## 3. Consentimiento y privacidad

Falta el texto legal definitivo, la versión inicial del aviso y la base de consentimiento aplicable a email y WhatsApp.

## 4. Ruta pública definitiva

La spec usa `/hablemos` como nombre conceptual. Confirmar slug antes de implementar enlaces y campañas.

## 5. Validaciones visuales

La estructura está definida, pero requieren comparación visual:

- ancho máximo del hilo;
- radio y densidad de globos;
- intensidad del coral en mensajes largos;
- composer;
- cards de CMS en mobile;
- tokens semánticos de foco, error, éxito y advertencia.

## 6. SLA comunicado

La conversación confirma que el equipo se pondrá en contacto, pero no debe prometer cuándo hasta que exista un SLA comercial documentado.

## 7. Contenido inicial de encaje

Antes del release hay que cargar y aprobar en CMS:

- servicios soportados;
- casos condicionales;
- necesidades no ofrecidas;
- respuestas y alternativas autorizadas;
- al menos un playbook publicable de punta a punta.
