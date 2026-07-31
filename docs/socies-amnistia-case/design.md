# Diseño del caso de cliente — Amnistía Internacional Argentina

## Trazabilidad

- `modelo_usado`: GPT-5 (Codex)
- `motivo_fallback`: el entorno no expone GPT-5.5 ni un selector de modelo superior para delegar la etapa de diseño.

## Propósito

Este documento define la primera referencia visual de la vista Cliente. Amnistía aporta contenido realista y una secuencia editorial concreta, pero la implementación usa exclusivamente bloques genéricos de categoría `Cliente` para que cualquier caso futuro pueda recombinar el mismo sistema.

## Dirección visual

La página se plantea como un **archivo editorial vivo de sistemas en funcionamiento**:

- fondo negro continuo y tipografía Manrope del sistema Socies;
- grilla visible, bordes finos y superficies multimedia grandes;
- color Socies usado solo para orientación, ritmo y estados;
- composición asimétrica, sin mockups de dispositivos ni cards genéricas;
- adornos Socies limitados a círculos, círculos partidos, triángulos, signos `+` y flechas;
- sin degradados ni tramas continuas; los adornos aparecen en composiciones recortadas contra los bordes;
- entre 70 % y 80 % de superficie visual cuando se cargue el material definitivo;
- placeholders grises honestos, con el texto exacto de producción pendiente;
- voz cercana y profesional, centrada en el acompañamiento y no en el stack.

No se incorpora amarillo de Amnistía como color estructural del caso. La identidad del cliente aparece mediante su logo, contenido y material real; la interfaz sigue siendo inequívocamente Socies.

## Hero propio de Cliente

El hero no es un bloque del builder. Pertenece a la vista `Cms/Client` y siempre se renderiza antes de los bloques.

### Contenido editable

- logo y nombre del cliente;
- volanta;
- título editorial;
- resumen;
- inicio o duración de la relación;
- capacidades aplicadas;
- una pieza principal intercambiable entre imagen y video.

### Composición

En mobile, identidad, título, resumen, metadatos y media se apilan en ese orden. Desde `md`, el contenido ocupa siete columnas y la media cinco. Dos composiciones de formas Socies entran al corte desde arriba a la derecha y abajo a la izquierda.

## Secuencia de bloques de referencia

1. `ClientMarquee`: pausa tipográfica con frases largas sobre el trabajo de Socies.
2. `ClientFeature`: plataforma o sistema con texto sticky y secuencia multimedia.
3. `ClientProjects`: dos o tres experiencias en una pista horizontal gobernada por scroll vertical.
4. `ClientFeature`: una segunda instancia del mismo bloque, invertida para alternar el ritmo.
5. `ClientStatement`: declaración humana de gran escala con media de apoyo.
6. `ClientProcess`: diagrama editorial de procesos e integraciones.
7. `ClientMetrics`: escala, responsabilidad operativa y evidencia visual.
8. `ClientTestimonial`: una o dos voces, con retrato o video intercambiable.
9. `ClientClosing`: mosaico final y llamado a la conversación.

El hero más estas nueve instancias forman diez momentos editoriales. Hay ocho tipos de bloque porque `ClientFeature` se reutiliza para dos contenidos diferentes. Ningún nombre, schema o comportamiento depende de Amnistía.

`ClientFeature`, `ClientStatement` y `ClientMetrics` exponen `layout` para alternar texto a izquierda o derecha sin crear variantes específicas por cliente.

## Sistema multimedia

Todo lugar que admite una imagen también admite video mediante `MediaPicker`:

- toggle `Imagen` / `Video`;
- carga de imagen o MP4/WebM/MOV;
- descripción accesible;
- texto de reemplazo;
- autoplay opcional para clips silenciosos, siempre con controles visibles.

Mientras el archivo no exista se muestra una superficie gris con el texto `Reemplazar por imagen/video de …`. El placeholder reserva proporción y evita layout shift.

## Movimiento

### Entrada

- identidad, título, resumen y metadatos aparecen en una timeline breve;
- la media del hero entra en paralelo con un desplazamiento contenido;
- no se bloquea el scroll.

### Scroll

- títulos, textos y piezas aparecen por bloque con stagger corto;
- los nodos del mapa de procesos se revelan siguiendo el orden de lectura;
- imágenes y videos tienen un desplazamiento vertical mínimo solo en desktop;
- el marquee usa movimiento lineal continuo;
- en desktop, `ClientProjects` queda fijado mientras el scroll vertical avanza su pista horizontal; no se muestra scrollbar;
- las métricas incrementan su parte numérica al entrar en viewport.

Solo se animan `transform` y `opacity`. Cada animación se limpia al desmontar la vista.

### Reducción de movimiento

Con `prefers-reduced-motion: reduce`:

- el contenido se muestra directamente;
- el marquee queda detenido;
- se elimina parallax, pinning y desplazamiento espacial;
- los proyectos se apilan y las métricas muestran su valor final;
- la lectura, los controles y el orden no cambian.

## Responsive y accesibilidad

- mobile first, verificado conceptualmente para 320, 768, 1024 y 1440 px;
- un solo `h1`, ubicado en el hero;
- encabezados de bloque en `h2` y elementos internos en `h3`;
- orden DOM igual al orden visual;
- videos con controles y descripción;
- foco visible en CTA;
- contraste basado exclusivamente en tokens del design system;
- ninguna información depende solo del color o del movimiento.

## Contenido pendiente

- Validar antes de publicar las métricas `30+` y `~10.000`.
- Sustituir cada placeholder con material aprobado y anonimizado.
- Confirmar retratos, cargos y autorización de testimonios.
- Revisar que capturas técnicas no expongan credenciales ni datos personales.
