# Socies Design System

## 1. Estado y autoridad

Este documento es la única fuente de verdad visual de Socies para diseñadores, desarrolladores y agentes de implementación.

- Estado: activo, con validaciones visuales pendientes señaladas explícitamente.
- Alcance: sitio público, páginas Inertia, recursos CMS y experiencia conversacional de Socies.
- Los tokens se implementan mediante CSS custom properties y se consumen desde Tailwind o componentes.
- No se deben hardcodear colores, fuentes, radios ni valores de marca en componentes.
- `design.md` en la raíz conserva únicamente una referencia a este documento.
- La especificación funcional de la conversación vive en `docs/socies-paco-spec/16_DISENO_Y_EXPERIENCIA.md`.

## 2. Contexto y personalidad

Socies ayuda a empresas y organizaciones a transformar procesos complejos en sistemas que funcionan.

La experiencia debe transmitir:

- claridad frente a problemas complejos;
- cercanía profesional, sin tono corporativo rígido;
- capacidad técnica sin convertir el stack en argumento comercial;
- criterio y acompañamiento;
- tecnología al servicio de problemas reales.

Evitar como lenguaje o dirección de marca:

- “software factory”, “squad”, “boutique” o “transformación disruptiva”;
- presentar a Socies como una agencia de IA;
- estética genérica de producto SaaS;
- una identidad visual separada para el agente conversacional.

## 3. Principios

1. **Oscuro por defecto.** La interfaz parte de fondo negro y texto blanco.
2. **Color con función.** Los colores Socies se reservan para logo, estados, indicadores y acentos puntuales.
3. **Una sola marca.** La experiencia conversacional se siente como hablar con el equipo de Socies.
4. **Mobile first.** Composición, controles y movimiento se diseñan primero para pantallas pequeñas.
5. **Accesible.** Objetivo mínimo WCAG 2.2 AA, teclado completo y reducción de movimiento.
6. **Sistema, no decorado.** Cada decisión visual debe tener una traducción clara a tokens, componentes o comportamiento.
7. **Contenido real.** El CMS controla qué servicios, trabajos y evidencias pueden mostrarse.

## 4. Sistema de color

### Tokens existentes

```css
--color-surface;
--color-text;
--color-primary;
--color-primary-hover;
--color-secondary;
--color-secondary-hover;
--color-secondary-light;
--color-black;
--color-white;
--color-gray;
--color-gray-2;
--color-gray-3;
--color-socies-green;
--color-socies-blue;
--color-socies-yellow;
--color-socies-coral;
--color-socies-violet;
--color-socies-aqua;
--color-focus;
--color-success;
--color-error;
--color-warning;
--paco-radius-panel;
--paco-radius-message;
--paco-radius-control;
--paco-thread-width;
```

### Reglas obligatorias

- Fondo general: `--color-black` mediante `--color-surface`.
- Texto general: `--color-white` mediante `--color-text`.
- No usar blanco como fondo general del sitio.
- Los estados monocromos del logo usan círculos blancos y glifos negros.
- Los mensajes de Socies en la conversación usan superficie negra, texto blanco y borde `--color-socies-green`.
- Los mensajes del visitante usan fondo `--color-socies-coral` y texto blanco.
- Estados de error, éxito, advertencia y foco usan respectivamente `--color-error`, `--color-success`, `--color-warning` y `--color-focus`.
- El color nunca puede ser el único medio para comunicar un estado.

### Requiere validación visual

- Intensidad exacta del coral en superficies amplias de mensajes.
- Relación entre borde verde, foco y estados de selección.
- Contraste de textos secundarios y placeholders sobre negro.

## 5. Tipografía

```css
font-family: 'Manrope', sans-serif;  /* cuerpo e interfaz */
font-family: 'Poppins', sans-serif;  /* alternativa disponible, no predeterminada */
font-family: 'Gotham Light', 'Gotham', sans-serif; /* título del hero */
```

Reglas:

- Manrope es la familia predeterminada para cuerpo, controles y experiencia conversacional.
- Gotham Light queda reservada al título del hero mientras esa pieza conserve su dirección aprobada.
- No introducir una familia nueva sin actualizar este documento y los tokens correspondientes.
- El texto del chat debe priorizar lectura rápida: líneas cortas, jerarquía contenida y sin bloques densos.
- No usar mayúsculas sostenidas en mensajes, formularios o ayudas.

## 6. Espaciado, grilla y contenedores

- Contenedor centrado con padding responsive.
- Padding de sección base: `py-12 md:py-16`.
- Separación de grilla base: `gap-6 md:gap-8`.
- La experiencia conversacional usa un ancho de lectura contenido dentro de la grilla general; no debe ocupar líneas de texto de extremo a extremo.
- Los controles móviles usan el ancho disponible y se reordenan antes de reducir su área táctil.
- La base implementada usa `--paco-thread-width: 52rem`, `--paco-radius-panel: 1.5rem`, `--paco-radius-message: 1.15rem` y `--paco-radius-control: 0.85rem`. Estos valores son ajustables únicamente aquí y en la capa de tokens, no dentro de componentes.

## 7. Breakpoints

```text
xs: 480px
sm: 640px
md: 768px
lg: 1024px
xl: 1080px
2xl: 1280px
3xl: 1340px
4xl: 1640px
5xl: 1920px
```

- No diseñar componentes únicamente para breakpoints discretos; deben funcionar fluidamente entre ellos.
- La conversación no puede depender de hover.
- En mobile, el composer respeta teclado virtual y safe areas.

## 8. Componentes base del sitio

- `<HomeHero>`: hero estático de la home, editable mediante constantes React.
- `<SociesLogo>`: SVG compartido entre hero y header.
- `<SiteHeader>`: header global de páginas Inertia.
- `<x-block>`: wrapper para recursos administrables mediante bloques.
- `<x-link>`: enlaces internos, externos y anclas en Blade.
- `<x-layout>`: layout de páginas Blade/CMS.
- `<SociesConversation>`: experiencia conversacional reutilizable en home y ruta propia.

Las páginas editoriales principales usan rutas Inertia y componentes React estáticos. El sistema de bloques se reserva para contenido que necesita composición dinámica.

## 9. Hero de la home

### Contenido y composición

- Ocupa el viewport completo.
- Contiene solo el logo animado y el título; no lleva bajada.
- El título usa Gotham Light, mayúsculas y tamaño contenido.
- El copy usa la palabra `sistemas`; toda esa palabra se muestra con `--color-socies-green`.
- Comienza con el ícono `arrow-down-right` en `--color-socies-coral` y termina con un círculo `--color-socies-yellow`.
- Ambos adornos ocupan un cuadrado del mismo tamaño que el texto y comparten padding óptico.

### Secuencia de entrada

- En cada carga completa, la `S` verde cae desde arriba, impacta y realiza exactamente dos rebotes pequeños.
- Las demás letras emergen desde la posición de la `S` y forman el logo centrado.
- Luego los círculos pasan a blanco y las letras a negro.
- El logo se desplaza hacia el borde inferior con offset adaptable.
- Una línea blanca de `1px` nace desde el centro del SVG y se extiende por detrás del logo.
- El título se revela letra por letra y renglón por renglón, respetando el wrapping real y sin recortar descendentes.
- La intro bloquea el scroll y se ejecuta en cada carga completa.
- Al volver a home mediante Inertia se muestra el estado final del logo y solo se anima el título.

### Scroll y header

- Al iniciar scroll, el texto sube y sale del hero; no permanece fijado al logo.
- El logo y la línea avanzan hacia el header.
- Al coincidir posición y tamaño con el logo del header, se intercambian instantáneamente, sin fade.
- El logo del header queda centrado horizontalmente.
- Fuera del punto superior permanece monocromático.
- En `scrollTop = 0`, terminada la intro, el hero también conserva el logo monocromático.
- Si pasan cuatro segundos desde el final de la intro sin scroll, aparece un `chevron-down` verde rebotando debajo del título.
- El indicador desaparece definitivamente al acumular los primeros 300 px de scroll.
- La secuencia se conserva en mobile con tamaños y offsets adaptativos.
- Con `prefers-reduced-motion: reduce` se muestra directamente el estado final.

## 10. Home y navegación

Orden base de la home:

1. hero;
2. logos de clientes;
3. conversación de Socies;
4. cómo trabajamos;
5. clientes y soluciones destacados;
6. CTA final;
7. footer.

Header inicial:

- logo de Socies;
- CTA principal: “Contanos tu problema”.

El CTA puede desplazar hacia la conversación en home o abrir su ruta propia según el contexto de navegación.

## 11. Experiencia conversacional

### Identidad

- “Paco” es un nombre interno de producto; no se presenta como un personaje independiente.
- La interfaz habla como Socies usando “nosotros”.
- No necesita avatar propio.
- Debe recordar a una app de mensajería por ritmo, alineación, continuidad y controles, sin copiar literalmente WhatsApp.

### Superficie

- Fondo negro continuo con el sitio.
- Mensajes de Socies alineados a la izquierda, borde verde y sin una superficie clara dominante.
- Mensajes del visitante alineados a la derecha, fondo coral y texto blanco.
- Formularios, selects, cards y feedback conservan los tokens Socies.
- El contenido del CMS se integra en el hilo sin convertirlo en un catálogo.
- Los textos extensos se dividen en párrafos con separación real; la pregunta principal ocupa un renglón propio, usa peso fuerte y se separa mediante un divisor verde sutil.
- Las cards de testimonio son compactas: avatar de la persona al inicio cuando exista, nombre y cargo junto a él, logo del cliente arriba a la derecha y cita breve. El nombre largo del cliente no se repite como encabezado visible.

### Ubicaciones

- Home: sección inline inmediatamente después de logos de clientes.
- Ruta propia: misma experiencia, con soporte de campaña y precarga segura.
- No se define un widget flotante para el MVP.

### Movimiento

- Aparición breve de mensajes y controles ya validados.
- Indicador de espera limitado; no introducir demoras artificiales largas.
- Auto-scroll solo cuando no interrumpe lectura o edición.
- No robar foco al aparecer un mensaje.
- Con reducción de movimiento, mostrar estados sin transiciones espaciales.

## 12. Formularios y controles

- Usar HTML nativo cuando resuelva correctamente el patrón.
- Usar primitives accesibles para select, dialog, slider u otros comportamientos complejos.
- Las librerías aportan comportamiento; la apariencia siempre se adapta a estos tokens.
- Labels visibles o nombres accesibles obligatorios.
- Errores vinculados al campo mediante semántica accesible.
- Área táctil mínima y foco visible en todos los controles.
- Nombre y contacto precargados deben poder revisarse antes del envío.
- No solicitar nuevamente un dato válido ya proporcionado por campaña o por el visitante.

## 13. Estados y feedback

La conversación debe diseñar explícitamente:

- inicial y precargada;
- enviando;
- Socies está respondiendo;
- respuesta recibida;
- validación de campo;
- error recuperable;
- rate limit o bloqueo;
- desconexión y reintento;
- edición de respuesta anterior;
- conversación suficiente y cerrada;
- contenido CMS no disponible.

El cierre es breve, cordial y confirma que el equipo se pondrá en contacto. No debe parecer un abandono ni ofrecer seguir preguntando si ya existe información suficiente.

## 14. Accesibilidad

- Objetivo WCAG 2.2 AA.
- `aria-live="polite"` para mensajes nuevos, sin anunciar toda la conversación.
- Orden DOM equivalente al orden visual.
- Navegación completa con teclado.
- Foco visible y restaurado de forma predecible después de dialogs o drawers.
- Mensajes y estados no dependen solo de alineación o color.
- Respeto obligatorio de `prefers-reduced-motion`.
- Video con controles y alternativa textual cuando exista.
- Contraste verificado sobre todos los pares de tokens usados.

## 15. Rendimiento

- No cargar librerías de IA en el navegador.
- Cargar la experiencia conversacional de forma diferida si está fuera del viewport inicial.
- Evitar librerías de animación adicionales para microinteracciones simples.
- Reservar dimensiones para media y cards para evitar layout shift.
- Mantener respuesta JSON atómica en el MVP.
- La UI debe seguir siendo utilizable en dispositivos móviles de gama media.

## 16. Qué evitar

- fondo general blanco;
- colores de marca usados simultáneamente sin una función;
- componentes con estética predeterminada de la librería;
- personaje, avatar o voz distinta de Socies;
- conversación abierta e interminable;
- catálogos extensos dentro del hilo;
- loaders prolongados o demoras teatrales;
- HTML producido por el modelo;
- valores visuales de marca hardcodeados;
- copiar literalmente patrones, assets o composición de WhatsApp.

## 17. Checklist de implementación

- [x] Todos los colores y fuentes provienen de tokens.
- [x] La home ubica la conversación después de logos.
- [x] La ruta propia reutiliza el mismo componente.
- [x] Socies habla en plural y no presenta un avatar separado.
- [ ] Los dos tipos de mensaje tienen contraste verificado.
- [x] Formularios y opciones funcionan con teclado.
- [x] Los mensajes nuevos se anuncian sin mover foco agresivamente.
- [ ] La experiencia contempla carga, error, reconexión, edición y cierre.
- [x] Mobile contempla teclado virtual y safe areas.
- [x] `prefers-reduced-motion` está implementado; resta validación visual en dispositivos reales.
- [ ] Las cards del CMS respetan publicación, permisos y encaje.

## 18. Pendientes de validación visual

- ajuste visual del ancho máximo del hilo y de cada globo sobre la base tokenizada;
- ajuste visual de radios, sombras y separación de mensajes sobre la base tokenizada;
- intensidad de coral en mensajes largos;
- forma final del composer y del indicador de espera;
- comportamiento exacto de cards y carruseles en mobile;
- contraste final de los tokens semánticos de foco, error, éxito y advertencia.
