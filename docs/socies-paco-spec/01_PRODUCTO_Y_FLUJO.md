# Producto y flujo conversacional

## 1. Definición del producto

Paco es el nombre interno del agente comercial de Socies. Para el visitante, quien conversa es el equipo de Socies usando “nosotros”. No es soporte, un asistente general ni un consultor que entrega una solución completa. Su objetivo es convertir una consulta inicial poco estructurada en un lead contactable y suficientemente calificado.

Debe equilibrar cuatro objetivos:

1. **Comprensión:** entender necesidad, organización y contexto.
2. **Confianza:** demostrar experiencia relevante con contenido real del CMS.
3. **Captura:** obtener nombre y un medio de contacto temprano.
4. **Calificación:** reunir solo los datos que cambian la decisión comercial.

## 2. Primer turno

Mensaje fijo:

> ¿En qué podemos ayudarte?

La respuesta es de texto libre. Ejemplo:

> Necesito desarrollar una landing page para una fundación ONG chica.

Si la respuesta es solo un saludo o no contiene un motivo identificable, Paco no avanza al playbook ni solicita datos de la organización. Vuelve a preguntar el motivo de consulta hasta obtener una necesidad mínima:

> Hola. ¿Cuál es el motivo de tu consulta? Contanos qué necesitás resolver o qué proyecto tenés en mente.

Estos intentos se registran para seguridad, pero no cuentan como interacciones útiles.

### Extracción esperada

| Dato | Valor | Tipo | Confianza aproximada |
|---|---|---|---:|
| necesidad | desarrollar una landing page | explícito | 0.99 |
| tipo de organización | fundación / ONG | explícito | 0.98 |
| tamaño | chica | explícito, significado relativo | 0.85 |
| capacidad presupuestaria | posiblemente baja | inferencia interna | 0.55 |
| tipo de proyecto | proyecto puntual | inferencia | 0.75 |
| intención comercial | solicitar desarrollo | inferencia fuerte | 0.90 |

La capacidad presupuestaria **no debe tratarse como un hecho**. “ONG chica” sirve para elegir ejemplos, packs accesibles y preguntas, pero no para afirmar que no puede pagar.

## 3. Etapas del flujo

### Etapa A — Descubrimiento inicial

Duración objetivo: 2 acciones del usuario.

- El usuario explica su necesidad.
- Paco confirma brevemente lo entendido.
- Paco hace una sola pregunta de alto valor para reducir ambigüedad.

Ejemplo:

> Entendimos: buscan una landing para presentar una iniciativa de la fundación. ¿Ya tienen definidos los contenidos y el objetivo principal?

La pregunta puede ser texto, select o multiselect según el playbook y la campaña.

### Etapa B — Captura de contacto

Después de dos interacciones útiles, salvo que el usuario ya haya entregado los datos. Saludos y respuestas sin información suficiente no adelantan esta captura.

Mensaje:

> Ya tenemos un buen punto de partida. Para continuar y poder responderte, compartinos tus datos de contacto.

Componente único:

- nombre;
- medio preferido: email o WhatsApp;
- valor correspondiente.

No se permite continuar anónimamente.

### Etapa C — Generación de confianza

Después de obtener el contacto y de comprender al menos una dimensión contextual del proyecto, Paco busca en el CMS:

- servicio o pack relacionado;
- uno o dos trabajos relevantes;
- cliente o rubro comparable;
- testimonio asociado;
- tecnologías, solo cuando aportan valor;
- tiempo estimado, solo si existe un dato vigente y aplicable.

La búsqueda de evidencia se intenta una vez por conversación, antes de realizar preguntas sensibles o cerrar. Obtener el contacto no alcanza para dispararla: debe existir al menos una respuesta útil posterior y contexto suficiente para distinguir el tipo de negocio, organización, problema o solución buscada. “Una web” y “aumentar ventas” no habilitan por sí solos una referencia comercial.

Cuando el pedido de una web sea genérico, Paco pregunta primero:

> ¿Para qué tipo de negocio u organización es la web y qué debería poder hacer?

Puede mostrar hasta tres proyectos comparables y un testimonio por turno. No debe convertir el chat en un catálogo.

Cuando exista una coincidencia fuerte y autorizada, el texto visible del mensaje debe incluir —no únicamente la tarjeta—:

1. nombre público del cliente;
2. problema o proyecto trabajado;
3. qué hizo Socies;
4. resultado documentado, si existe;
5. testimonio breve y atribución exacta, si está autorizado;
6. enlace o tarjeta del trabajo.

Prioridad editorial del retrieval:

1. mismo problema y mismo rubro;
2. mismo problema en otro rubro;
3. mismo tipo de solución;
4. testimonio relacionado;
5. servicio general, solo si no hay casos.

No usar una formulación genérica si existe un cliente autorizado y suficientemente relacionado. Si no existe evidencia publicable aplicable, Paco no expone la búsqueda ni habla de “casos publicables”. Continúa de forma natural —por ejemplo, “Gracias, Ana. Para entender mejor tu caso…”— o cierra indicando que el equipo analizará el enfoque y retomará el contacto.

Cuando existan varios proyectos sólidos, Paco puede agrupar hasta tres clientes para demostrar amplitud sin convertir el mensaje en una lista exhaustiva. Después puede destacar un testimonio autorizado que refuerce la capacidad más relevante para el caso.

Ejemplo:

> Sí, Luciano. Hemos realizado proyectos de estas características para Fundación Huésped, Amnistía Internacional y CEDES. Además, Florencia Gadea, Directora de División de Comunicación de Fundación Huésped, destacó el acompañamiento y las soluciones creativas del equipo. Te invitamos a ver el testimonio completo. Para entender el alcance, ¿qué rol tenés en la decisión?

Los nombres, cargos, proyectos y resultados del ejemplo solo pueden aparecer cuando las piezas correspondientes estén autorizadas en el CMS. El cargo se conserva tal como fue publicado; no se reemplaza por una denominación aproximada como “Directora general”.

### Etapa D — Calificación dinámica

Cantidad objetivo: 1 a 3 preguntas después del contacto.

Las preguntas dependen de la necesidad. No existe un formulario universal.

El playbook es una guía y un límite editorial, no un formulario lineal. Paco puede:

- cambiar el orden según lo ya dicho;
- omitir preguntas que no cambien el diagnóstico;
- incorporar una pregunta activa del catálogo general cuando el caso la vuelva relevante;
- responder primero una duda u objeción del visitante y luego retomar;
- cerrar antes de agotar el playbook cuando la información sea suficiente.

Las preguntas `required` siguen siendo obligatorias. Los máximos se calculan sobre interacciones útiles; saludos, aclaraciones fallidas, dudas y objeciones no deben provocar un cierre prematuro.

Para una landing pueden importar:

- objetivo de la landing;
- contenidos y diseño disponibles;
- fecha vinculada a campaña o evento;
- responsable de decisión;
- si necesita mantenimiento o solo publicación.
- relación de la persona que consulta con el proyecto;
- si el proyecto corresponde a una persona independiente, un equipo o una organización.

Para un servicio mensual pueden importar:

- frecuencia de trabajo;
- equipo interno;
- volumen de pedidos;
- herramientas actuales;
- rol del contacto.

Para un sistema a medida también importan la escala operativa y, de forma opcional, si existe un rango de inversión previsto. El presupuesto nunca se usa por sí solo para descartar una consulta.

No se pregunta un dato si no afecta el diagnóstico, el fit o la próxima acción.

### Etapa E — Cierre

Paco resume en una o dos frases y confirma que el equipo revisará el caso.

Ejemplo:

> Gracias, Ana. Ya tenemos un panorama claro de la landing y de la campaña. Nuestro equipo va a revisar el caso y te contactaremos por WhatsApp.

Estado final inicial:

```text
pending_review
```

Paco nunca comunica al visitante su score ni si fue clasificado como lead bajo, medio o alto.

## 4. Prioridad de datos

### Obligatorios

1. motivo de consulta;
2. descripción o bajada suficiente;
3. nombre;
4. email o WhatsApp.

### Alta prioridad dinámica

- organización;
- cargo o rol;
- objetivo;
- situación actual;
- tipo de solución;
- poder de decisión;
- escala relevante para el servicio.

### Prioridad media

- cantidad de empleados;
- facturación;
- urgencia;
- fecha;
- herramientas existentes;
- presupuesto si el usuario lo menciona.

### Baja prioridad

- datos que no cambian el enfoque;
- datos inferibles de manera confiable desde campaña, página, IP o CMS;
- curiosidades no comerciales.

## 5. Reglas de agilidad

- Una pregunta principal por mensaje.
- Máximo sugerido: 7 acciones del usuario.
- Máximo duro inicial: 10 acciones del usuario.
- Máximo sugerido después del contacto: 3 preguntas.
- Dos respuestas consecutivas sin información útil activan un cierre asistido.
- Una salida de tema permite una redirección; la reincidencia habilita cierre cordial.
- No repetir preguntas ya contestadas explícita o implícitamente.
- Si el usuario modifica una respuesta anterior, recalcular el estado y el scoring.

## 6. Campañas y contexto

Una campaña puede configurar:

- mensaje inicial alternativo;
- intención o servicio probable;
- preguntas prioritarias;
- contenidos a favorecer;
- pack sugerido;
- límites de conversación;
- campos de calificación;
- tono o CTA final.

La conversación existe en home y en una ruta propia. La ruta acepta parámetros no sensibles como `campaign`, `intent`, `source` y UTMs. Nombre, email y consulta inicial se precargan mediante un `prefill_token` opaco respaldado por caché, no como PII en texto plano dentro de la URL.

Los valores precargados:

- se muestran antes de confirmarse;
- pueden editarse;
- no se convierten en hechos hasta que el visitante los acepta;
- no vuelven a preguntarse si ya fueron confirmados y son válidos;
- se persisten como eventos normales después de crear la conversación.

Ejemplo: una campaña de landings puede iniciar con texto libre, pero enviar al agente contexto interno:

```json
{
  "campaign": "landing-pages-ong",
  "likely_intent": "landing_page",
  "preferred_playbook": "landing_project",
  "preferred_content_tags": ["landing", "ong", "fundaciones"]
}
```

## 7. Recuperación de una conversación

- El cliente recibe un identificador opaco de conversación.
- El navegador puede conservarlo para retomar desde el mismo dispositivo.
- El backend conserva el JSON completo y el estado normalizado.
- El equipo puede revisar la conversación aunque el visitante no la retome.
- La reanudación externa días después es una capacidad posterior al MVP, pero el modelo de datos debe soportarla.
- Una consulta inicial todavía no enviada puede conservarse temporalmente en `sessionStorage`; nombre, email y teléfono no se guardan allí.
