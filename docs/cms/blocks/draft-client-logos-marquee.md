# Draft de bloque: ClientLogosMarquee

## Meta

- **Nombre:** `ClientLogosMarquee`
- **Categoría:** Área / Home estática
- **Label:** Logos de clientes en movimiento
- **Ubicación:** inmediatamente después de `HomeHero` en la página Home de Inertia.

> Este no será un bloque del Page Builder: el design system define la home como una ruta React estática. El componente se alimenta de los clientes precargados que tengan logo, sin duplicar los archivos ni convertirlos en props editables.

## Schema

| Campo | Tipo | Requerido | Default | Notas |
|---|---|---:|---|---|
| `clients` | colección calculada | sí | clientes publicados con `logo` | Entregada por `HomeController`: identidad, primer testimonio y canales de preview. |
| `speed` | constante del componente | sí | `40` | Conserva la velocidad indicada en la maqueta. |
| `pauseOnHover` | constante del componente | sí | `true` | Detiene el recorrido al pasar el cursor; no afecta teclado ni touch. |
| `autoFill` | constante del componente | sí | `true` | Repite los logos hasta cubrir el ancho disponible. |

## Comportamiento

- Renderiza sólo clientes publicados que tengan logo; los borradores y los clientes sin logo no aparecen.
- Muestra los logos procesados en blanco, con fondo transparente y altura visual unificada.
- La franja mantiene fondo negro, texto accesible oculto para describir la sección y degradados laterales sutiles para el recorrido continuo.
- Cada logo ocupa una celda rectangular sin borde, dimensiones consistentes y una escala visual amplia; las celdas son contiguas.
- En desktop, cada logo abre en hover/foco un popup Tippy con `followCursor: true`; al hacer click se abre el mismo contenido como modal.
- En dispositivos touch, el contenido se abre directamente como modal y se cierra con el botón, `Escape` o tocando el backdrop.
- El popup usa el color del cliente en la cabecera y `popup_text_color` (`black` o `white`) para el contraste del título.
- La pantalla inferior permanece negra aun cuando el cliente no tenga contenido.
- El tab `Preview` define la secuencia completa con un repeater ordenable. Cada item puede ser testimonio, imagen o video; el primer item es el primer canal.
- Un testimonio sin duración personalizada se muestra durante 1000 ms y comienza con una comilla Lucide en rosa Socies.
- Cada canal de preview acepta imagen o video y una duración opcional. Una imagen sin duración usa 1000 ms; un video sin duración usa automáticamente su duración natural.
- Los videos se reproducen automáticamente, sin sonido y con `playsInline`.
- Entre canales se reproduce un fragmento aleatorio de 200 a 500 ms del clip de lluvia de TV optimizado en `public/media/client-story-static.mp4`.
- En `prefers-reduced-motion`, se desactiva el desplazamiento y los logos quedan disponibles en una fila desplazable horizontalmente.
- Cada logo no es enlazable por ahora; se puede habilitar su enlace a `/cliente/{slug}` sin cambiar el componente visual.

## Estructura inferida de la maqueta

| Slot en HTML | Implementación React | Notas |
|---|---|---|
| `section` full width con padding vertical | `section` debajo de `HomeHero` | La tira ocupa todo el viewport; sólo conserva el spacing vertical de sección. |
| `Marquee` continuo | dependencia liviana o animación CSS equivalente | Debe respetar reducción de movimiento. |
| wrapper por logo | celda rectangular contigua | Sin gaps, bordes, sombras ni fondos individuales. |
| `img` | logo WebP desde `storage` | `object-contain`, `alt` con el nombre real del cliente. |

## Notas de implementación

- No se agrega `react-fast-marquee`: no está instalado. Se resolverá con una animación CSS pequeña para evitar una dependencia nueva y respetar `prefers-reduced-motion`.
- Implementado en `ClientLogosMarquee.jsx`, con estilos en `resources/css/components/client-logos.css`.
- Las URLs de logos se resolverán con `Storage::url()` en backend.
- `Home.jsx` conserva una lista estática de respaldo con los clientes públicos actuales para que el bloque también exista en renders sin props; si Laravel entrega `clients`, esa lista filtrada tiene prioridad.
- El pin del hero no reserva espacio adicional: el marquee entra inmediatamente al terminar el recorrido de la línea y el logo.
- Referencias visuales obligatorias: `docs/ux/design-system.md` y `design.md`.
