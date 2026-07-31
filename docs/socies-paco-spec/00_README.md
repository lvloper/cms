# Paco — Especificación funcional y técnica

**Proyecto:** chat comercial inteligente de Socies  
**Estado:** especificación consolidada previa al desarrollo  
**Fecha de referencia:** 2026-07-31

## Objetivo

Paco reemplaza al formulario de contacto tradicional de Socies. Debe:

1. entender qué necesita el visitante a partir de texto libre;
2. inferir contexto útil sin presentar las inferencias como hechos;
3. obtener nombre y un medio de contacto rápidamente;
4. mostrar servicios, trabajos, clientes, packs y testimonios relevantes;
5. realizar pocas preguntas adicionales para calificar el lead;
6. guardar el progreso después de cada interacción;
7. cerrar siempre como `pending_review` para revisión del equipo;
8. generar un resumen comercial y conservar la conversación completa.

## Experiencia esperada

La interfaz utiliza patrones familiares de mensajería, sin copiar literalmente WhatsApp. Habla Socies como equipo y dentro del hilo puede mostrar controles estructurados:

- botones;
- selección única;
- selección múltiple;
- texto libre;
- email;
- teléfono;
- rango o slider;
- fecha;
- tarjetas de servicios, packs y casos;
- testimonios;
- imágenes y videos.

No se muestra una barra de progreso. Se permite volver atrás y modificar respuestas. Solo pueden omitirse preguntas no obligatorias.

La conversación aparece inline en la home después de logos y también tiene una ruta propia para campañas y precarga segura.

## Principios no negociables

- Primer mensaje: **“¿En qué podemos ayudarte?”**, con campo libre.
- Después de aproximadamente dos interacciones se solicita nombre y email o WhatsApp.
- Nombre y un medio de contacto son obligatorios para continuar.
- Una sola pregunta principal por turno.
- Respuestas muy breves, cálidas, consultivas y directas.
- Paco habla como Socies, en primera persona plural.
- “Paco” es un nombre interno; no se presenta un personaje o avatar separado.
- Puede usar pocos emojis, nunca como decoración constante.
- No informa precios salvo packs publicados con precio vigente.
- No informa tiempos salvo dato documentado; siempre los presenta como aproximados.
- No promete resultados, disponibilidad ni experiencia no documentada.
- No inventa clientes, proyectos, testimonios, tecnologías ni métricas.
- Todo dato inferido conserva evidencia y nivel de confianza.
- Los datos se guardan progresivamente; el valor del lead no depende de completar todo el flujo.
- No existe toma de conversación en vivo ni intervención humana dentro del chat.

## Supuestos técnicos iniciales

- Backend principal: Laravel 13.
- Base de datos: PostgreSQL.
- Búsqueda semántica: `pgvector`.
- Frontend: React.
- CMS y agente se desarrollan en paralelo.
- API server-driven: el backend decide el próximo turno y React solo renderiza componentes permitidos.
- Respuesta atómica JSON en el MVP; no streaming token por token.
- Redis recomendado para locks, caché y rate limiting de aplicación.
- Cloudflare delante del endpoint público.

## Mapa de documentos

| Archivo | Contenido |
|---|---|
| `01_PRODUCTO_Y_FLUJO.md` | Objetivo comercial, etapas y experiencia |
| `02_ORQUESTACION_DEL_AGENTE.md` | Pipeline de Paco y máquina de estados |
| `03_CMS_Y_BASE_DE_CONOCIMIENTO.md` | Entidades, bloques aprobados y embeddings |
| `04_MODELO_DE_DATOS.md` | Tablas y persistencia |
| `05_CONTRATOS_API_Y_UI.md` | JSON de entrada, salida y componentes |
| `06_PROMPTS_Y_GUARDRAILS.md` | Instrucciones, límites y evidencia |
| `07_CALIFICACION_DE_LEADS.md` | Datos, scoring y clasificación |
| `08_FRONTEND_REACT.md` | Arquitectura del widget y componentes |
| `09_BACKEND_E_IA.md` | Laravel, modelos, tools y recuperación |
| `10_SEGURIDAD_Y_PRIVACIDAD.md` | Abuso, PII, Cloudflare y límites |
| `11_TESTING_Y_EVALS.md` | Pruebas funcionales y evaluaciones de IA |
| `12_ROADMAP_MVP.md` | Fases y criterios de aceptación |
| `13_DECISIONES_ABIERTAS.md` | Decisiones pendientes con defaults propuestos |
| `14_EJEMPLO_LANDING_ONG.md` | Conversación y estado interno de ejemplo |
| `15_LIBRERIAS_Y_DECISIONES_TECNICAS.md` | Stack elegido y adopción por fases |
| `16_DISENO_Y_EXPERIENCIA.md` | Ubicación, campañas, precarga y comportamiento visual |
| `17_SEEDS_INICIALES.md` | Datos mínimos e idempotentes para iniciar el proyecto |
| `18_IMPLEMENTACION_ACTUAL.md` | Qué está ejecutable, esquema visual, tablas y límites actuales |
| `adrs/` | Alternativas técnicas, motivos de descarte y reevaluación |

## Cómo usar esta carpeta con el agente de código

1. Leer primero este archivo, `18_IMPLEMENTACION_ACTUAL.md` y `01_PRODUCTO_Y_FLUJO.md`.
2. Tratar `05_CONTRATOS_API_Y_UI.md` como contrato compartido entre backend y frontend.
3. No implementar una ruta libre del modelo hacia SQL, HTML o componentes arbitrarios.
4. Registrar cualquier cambio de arquitectura en `13_DECISIONES_ABIERTAS.md` antes de codificarlo.
5. Construir una vertical completa antes de ampliar entidades o componentes.
6. Tratar `docs/ux/design-system.md` como única fuente de verdad visual.
