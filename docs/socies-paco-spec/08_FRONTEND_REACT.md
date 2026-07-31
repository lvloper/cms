# Frontend React

## 1. Responsabilidad de la experiencia

El componente reutilizable no decide la estrategia comercial. Se monta inline en home y en una ruta propia. Debe:

- crear o recuperar conversación;
- enviar acciones;
- renderizar turnos y componentes permitidos;
- validar datos básicos;
- permitir volver atrás;
- manejar loading, errores y reconexión;
- conservar el token opaco;
- enviar contexto de página y campaña;
- registrar eventos de UI.
- cargar y permitir editar una precarga segura.

## 2. Arquitectura sugerida

```text
SociesConversation
├── PacoProvider
├── ConversationShell
│   ├── MessageList
│   ├── AssistantTurn
│   ├── UserTurn
│   ├── ContentRenderer
│   └── ComponentRenderer
├── ComposerArea
├── BackAction
└── StatusAnnouncer
```

### Registro de componentes

```ts
const componentRegistry = {
  text_input: TextInputPart,
  buttons: ButtonsPart,
  single_select: SingleSelectPart,
  multi_select: MultiSelectPart,
  contact_form: ContactFormPart,
  range: RangePart,
  slider: SliderPart,
  date: DatePart,
  content_carousel: ContentCarouselPart,
  testimonial: TestimonialPart,
  video: VideoPart,
};
```

Un tipo desconocido debe renderizar fallback y reportar error; nunca ejecutar código enviado por backend.

## 3. Estado local

TanStack Query administra estado remoto y mutaciones. `useReducer` o Context alcanzan para estado visual local. El servidor es la fuente de verdad.

Estado mínimo:

```ts
type PacoState = {
  conversationId?: string;
  conversationToken?: string;
  version: number;
  status: 'idle' | 'submitting' | 'active' | 'closed' | 'error';
  turns: UiTurn[];
  pendingAction?: PacoAction;
  canGoBack: boolean;
};
```

No duplicar en frontend el scoring, el playbook ni la lógica de etapas.

## 4. Envío atómico

En el MVP, no usar streaming de tokens:

- los mensajes son cortos;
- la salida incluye JSON y componentes;
- el backend debe validarla completa;
- una respuesta parcial no puede habilitar un formulario inválido.

Se puede simular una sensación conversacional con:

- indicador “Paco está escribiendo”;
- delay visual breve y limitado;
- aparición progresiva por partes ya validadas.

No introducir demoras artificiales largas.

## 5. Formularios

Usar schemas compartidos o generados desde contrato.

- React Hook Form para inputs y formularios.
- Zod para validación en cliente.
- El backend vuelve a validar siempre.
- Email y teléfono se normalizan en backend.
- Para WhatsApp, usar E.164 internamente.

## 6. UI y accesibilidad

Usar HTML nativo cuando sea suficiente y Radix Primitives para comportamiento complejo. Shadcn puede aportar componentes puntuales copiados y adaptados después de comprobar compatibilidad con el stack. Ninguna librería define la estética: se aplica `docs/ux/design-system.md`.

Requisitos:

- todos los campos tienen label accesible;
- mensajes nuevos se anuncian con `aria-live="polite"`;
- no mover el foco automáticamente de forma agresiva;
- opciones utilizables con teclado;
- errores ligados al campo;
- contraste y tamaño táctil adecuados;
- videos con controles y alternativa textual;
- respetar `prefers-reduced-motion`.

## 7. Estilos y montaje

Preparar:

- CSS variables de Socies;
- prefijo de clases;
- módulo compartido para home y ruta propia;
- ancho y altura adaptables;
- modo inline y página completa sobre la misma base;
- sin depender de estilos globales del host.

Vite puede separar el módulo y cargarlo de forma diferida. Un build embebible externo queda como capacidad futura:

```text
chunk para la home Inertia
chunk para la ruta propia
ES module externo futuro
```

## 8. Persistencia del navegador

Guardar únicamente:

```text
conversation_id
conversation_token
last_seen_version
campaign
```

Antes de crear la conversación se puede guardar solo el borrador de consulta inicial en `sessionStorage`. No guardar nombre, email, teléfono, transcript ni `prefill_token` persistente.

No guardar email, teléfono ni transcript completo en `localStorage`.

La aplicación puede usar cookie first-party o almacenamiento local según el modo de embebido y la política de privacidad.

## 9. Volver atrás

“Volver” no borra historia. Genera una acción:

```json
{
  "type":"revise_answer",
  "target_event_id":"evt_uuid",
  "new_value":"..."
}
```

El backend:

1. registra la revisión;
2. invalida atributos derivados cuando corresponde;
3. recalcula estado y score;
4. genera el próximo turno.

## 10. Eventos analíticos

```text
widget_opened
conversation_started
action_submitted
contact_requested
contact_submitted
content_viewed
content_clicked
question_skipped
answer_revised
conversation_closed
api_error
```

No enviar PII a herramientas analíticas de terceros.

## 11. Paquetes iniciales

```text
react
react-dom
typescript
vite
@tanstack/react-query
zod
react-hook-form
@hookform/resolvers
radix-ui o primitives individuales según necesidad
libphonenumber-js
react-textarea-autosize
```

Evitar en el widget inicial:

- un framework completo de chatbot;
- estado global pesado;
- Markdown con HTML;
- librerías de animación grandes;
- un SDK de IA en el navegador;
- acceso directo del browser al proveedor de modelos.
- un runtime genérico de chatbot.
