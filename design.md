Quiero que me ayudes a construir el archivo `design.md` del sitio web de **Socies**, pero hay una condición fundamental:

## No tomes decisiones de branding, dirección visual ni diseño por tu cuenta

Tu tarea no es inventar una identidad ni completar decisiones faltantes con “buenas prácticas” genéricas.

Tu tarea es:

1. detectar qué decisiones faltan;
2. consultármelas de forma ordenada;
3. ayudarme a elegir entre alternativas concretas;
4. registrar cada decisión;
5. detectar contradicciones;
6. generar el `design.md` sólo cuando las decisiones importantes estén resueltas.

No asumas colores, tipografías, estilos, layouts, animaciones, componentes ni tono visual sin mi aprobación explícita.

Cuando una decisión todavía no esté definida, marcala como pendiente.

---

# Decisiones visuales confirmadas

Estas reglas ya fueron aprobadas y funcionan como punto de partida del sitio:

## Base visual

* El sitio usa siempre fondo negro y texto blanco por defecto.
* No se utiliza un fondo general blanco.
* Los colores de Socies se reservan para el logo, indicadores y acentos puntuales.
* La home usa una ruta normal de Inertia y componentes React estáticos.
* El contenido del hero se edita mediante constantes en su archivo React, no desde el CMS.
* El sistema de bloques se reserva para recursos que realmente necesiten composición dinámica, como proyectos o casos similares.

## Hero de la home

* El hero ocupa el viewport completo.
* Contiene únicamente el logo animado y el título; no lleva bajada.
* En cada carga completa de la home, la `S` verde cae desde arriba, impacta y realiza exactamente dos rebotes pequeños.
* Las demás letras emergen desde la posición de la `S` y forman el logo centrado.
* Luego los círculos pasan a blanco y las letras a negro.
* El logo se desplaza hacia el borde inferior, con un offset adaptable a la pantalla.
* Una línea blanca de `1px` nace desde el centro del SVG y se extiende por detrás del logo.
* El título usa Gotham Light, mayúsculas y un tamaño contenido.
* El copy usa la palabra `sistemas`; toda la palabra se muestra en verde Socies.
* El título se revela letra por letra y renglón por renglón, respetando el wrapping real de cada viewport y sin recortar descendentes como `g`, `j` o `q`.
* El título comienza con el ícono `arrow-down-right` coral de Lucide y termina con un círculo amarillo. Ambos adornos ocupan un cuadrado del mismo tamaño que el texto y comparten el mismo padding óptico dentro de la grilla.
* La presentación principal bloquea el scroll y se ejecuta en cada carga completa de la página.
* Si el usuario navega mediante Inertia y vuelve a la home, se muestra directamente el estado final del logo y sólo se anima el título.
* Durante el scroll, el logo del hero y su línea avanzan hacia el header.
* Al comenzar el scroll, el bloque de texto se desplaza hacia arriba y sale del hero; no permanece fijado junto al logo.
* Al coincidir posición y tamaño con el logo del header, ambos logos se intercambian instantáneamente, sin fade.
* El logo del header queda centrado horizontalmente.
* Si pasan cuatro segundos desde el final de la animación sin scroll, aparece un `chevron-down` verde Socies rebotando, pegado debajo del título y con el mismo tamaño tipográfico. Desaparece definitivamente cuando el usuario acumula los primeros 300 px de scroll.
* Fuera del punto superior, el logo del header se mantiene monocromático: círculos blancos y letras negras.
* En `scrollTop = 0`, una vez finalizada la intro, el hero también conserva el logo monocromático.
* La animación completa se mantiene en mobile con tamaños y offsets adaptativos.
* Con reducción de movimiento activada se presenta directamente el estado final.

---

# Contexto del proyecto

Socies ayuda a empresas y organizaciones a transformar procesos complejos en sistemas que funcionan.

Servicios principales:

1. Sistemas a medida.
2. Automatización e integraciones.
3. Evolución y soporte de plataformas existentes.
4. Capacidad técnica para agencias, consultoras o socios comerciales.

La inteligencia artificial puede integrarse cuando aporta valor, pero Socies no debe presentarse como una agencia de IA.

La comunicación debe enfocarse en problemas reales:

* procesos manuales;
* información dispersa;
* sistemas desconectados;
* herramientas que quedaron cortas;
* software que necesita evolucionar;
* tareas repetitivas;
* falta de control o trazabilidad;
* necesidad de acompañamiento técnico.

Evitar lenguaje innecesariamente técnico o corporativo:

* software factory;
* squad;
* MVP;
* boutique;
* transformación disruptiva;
* innovación de punta;
* stacks como argumento comercial.

---

# Arquitectura actual

La navegación principal será mínima.

Header inicial:

* Logo de Socies.
* CTA principal: “Contanos tu problema”.

La home tendrá:

1. Hero de impacto visual.
2. Logos de clientes.
3. Navegador inteligente.
4. Bloque breve de cómo trabajamos.
5. Clientes y soluciones destacados.
6. CTA final.
7. Footer.

El sitio también tendrá páginas o recursos para:

* soluciones;
* clientes y soluciones;
* contacto.

“Cómo trabajamos” no tendrá inicialmente una página extensa.

---

# Navegador inteligente

La experiencia principal será una conversación guiada.

Debe sentirse más cerca de una interfaz de mensajería que de un Typeform, pero sin copiar literalmente WhatsApp.

Puede combinar:

* mensajes;
* respuestas rápidas;
* selección simple;
* selección múltiple;
* campos de texto;
* formularios breves;
* carga de archivos;
* datos de contacto;
* resumen de respuestas;
* posibilidad de volver y modificar respuestas.

No será inicialmente un chatbot completamente abierto.

Su función será:

* entender la situación;
* identificar el problema;
* recomendar una o varias soluciones;
* mostrar clientes con experiencias relacionadas;
* explicar cómo se podría avanzar;
* llevar a un contacto contextualizado.

---

# Clientes y soluciones

No habrá necesariamente un caso separado por cada proyecto.

Cada cliente podrá presentarse como una historia completa de relación:

* contexto;
* cómo comenzó la relación;
* problemas resueltos;
* proyectos realizados;
* mejoras;
* timeline;
* capacidades aplicadas;
* evolución;
* continuidad;
* resultados;
* testimonial si existe.

Una página de cliente puede agrupar muchos trabajos pequeños o medianos.

---

# Objetivo del `design.md`

El documento final debe servir como guía concreta para diseñadores, desarrolladores y agentes de implementación.

Debe definir, una vez que yo haya tomado las decisiones:

* dirección visual;
* branding aplicado a producto digital;
* principios de diseño;
* tipografía;
* color;
* grillas;
* contenedores;
* espaciados;
* layouts;
* responsive;
* mobile first;
* hero;
* navegación;
* animaciones;
* comportamiento con scroll;
* navegador inteligente;
* componentes;
* páginas;
* formularios;
* imágenes;
* iconografía;
* accesibilidad;
* rendimiento;
* estados;
* tokens;
* restricciones.

No debe ser un documento abstracto.

Cada regla debe poder traducirse a una decisión de interfaz o código.

---

# Tu forma de trabajo

## 1. No generar el archivo todavía

Primero hacé una breve auditoría de lo que ya está definido y de lo que todavía falta decidir.

Separá claramente:

* decisiones ya tomadas;
* decisiones parcialmente tomadas;
* decisiones pendientes;
* contradicciones o riesgos.

No conviertas preferencias generales en decisiones cerradas.

Por ejemplo:

“Queremos una web artística” no define por sí solo:

* la paleta;
* la tipografía;
* el tipo de composición;
* la intensidad de las animaciones;
* la densidad;
* el estilo de imágenes.

---

## 2. Consultarme por rondas

Haceme preguntas en rondas temáticas.

Máximo 10 preguntas por ronda.

No avances a la siguiente ronda hasta que yo responda la actual.

Cada pregunta debe resolver una sola decisión.

No mezcles dos o más decisiones en una misma pregunta.

Ejemplo incorrecto:

> ¿Preferís una web oscura con tipografía grande y animaciones fuertes?

Ejemplos correctos:

> ¿Qué base de color preferís para la interfaz?

> ¿Qué nivel de protagonismo debe tener la tipografía?

> ¿Qué intensidad de movimiento querés en el hero?

---

## 3. Dar opciones concretas

Siempre que sea posible, presentá entre 2 y 4 opciones claramente diferenciadas.

No uses opciones vagas como:

* moderno;
* elegante;
* innovador;
* creativo.

Explicá cada opción de forma práctica:

* cómo se vería;
* qué sensación produciría;
* qué ventajas tiene;
* qué riesgos tiene;
* cómo funcionaría en mobile.

Permití también una respuesta personalizada.

---

## 4. No recomendar automáticamente una opción

Podés explicar ventajas y riesgos, pero no cierres la decisión por mí.

No digas:

> La mejor opción es B.

Usá:

> La opción B prioriza esto, pero implica este riesgo.

Sólo podés hacer una recomendación cuando yo te la pida explícitamente.

---

## 5. Registrar las decisiones

Después de cada ronda, devolvé una tabla o resumen con:

* decisión;
* respuesta elegida;
* implicación;
* estado.

Estados posibles:

* definido;
* parcialmente definido;
* pendiente;
* requiere validación visual.

Ejemplo:

| Decisión        | Elección    | Implicación                             | Estado    |
| --------------- | ----------- | --------------------------------------- | --------- |
| Base cromática  | Fondo claro | Mayor legibilidad y sensación editorial | Definido  |
| Color de acento | Sin definir | Afecta CTA, enlaces y estados           | Pendiente |

No cambies después una decisión sin avisar.

---

## 6. Detectar contradicciones

Cuando una nueva respuesta entre en conflicto con una decisión previa, señalalo.

Ejemplo:

> Antes definimos una experiencia sobria y de bajo movimiento. Ahora elegiste animaciones intensas en todas las secciones. ¿Querés priorizar una de las dos ideas o limitar el movimiento sólo al hero?

No resuelvas la contradicción por tu cuenta.

---

## 7. Usar referencias correctamente

Si te comparto:

* wireframes;
* capturas;
* sitios;
* videos;
* moodboards;
* componentes;
* imágenes;

analizalos como referencias parciales.

Preguntame específicamente qué quiero conservar de cada uno:

* composición;
* ritmo;
* tipografía;
* color;
* interacción;
* animación;
* estructura;
* sensación;
* transición.

No asumas que quiero copiar toda la referencia.

No copies literalmente diseños, assets ni interacciones propietarias.

---

# Orden sugerido de las rondas

## Ronda 1: personalidad de marca

Preguntame sobre:

* percepción deseada;
* nivel de formalidad;
* nivel de expresividad;
* cercanía;
* autoridad;
* relación entre tecnología y humanidad;
* atributos que no debe transmitir.

El objetivo es definir cómo debe sentirse Socies, no elegir todavía colores o fuentes.

---

## Ronda 2: dirección visual

Preguntame sobre:

* editorial versus interfaz de producto;
* minimalismo versus expresividad;
* orgánico versus geométrico;
* sobrio versus experimental;
* densidad;
* uso del espacio vacío;
* contraste;
* predominio de texto o visuales.

---

## Ronda 3: color

Preguntame por separado:

* fondo principal;
* base clara, oscura o mixta;
* cantidad de colores;
* color de acento;
* uso de color por secciones;
* gradientes;
* contraste;
* estados;
* color del navegador inteligente.

No propongas códigos hexadecimales definitivos hasta que la dirección esté elegida.

---

## Ronda 4: tipografía

Preguntame sobre:

* personalidad de titulares;
* personalidad del cuerpo;
* serif o sans;
* contraste entre familias;
* cantidad de familias;
* escala;
* peso;
* ancho;
* mayúsculas;
* estilo editorial;
* uso de variable fonts.

No elijas fuentes concretas hasta que yo apruebe la dirección.

Después podés mostrar candidatos para que yo elija.

---

## Ronda 5: composición y grilla

Preguntame sobre:

* ancho de contenido;
* grilla;
* alineación;
* simetría;
* asimetría;
* tamaños de bloques;
* secciones a pantalla completa;
* superposiciones;
* bordes;
* radios;
* separadores;
* ritmo vertical.

---

## Ronda 6: hero

Preguntame sobre:

* objetivo emocional;
* protagonismo del texto;
* tipo de visual;
* relación entre animación y mensaje;
* duración;
* primer viewport;
* CTA;
* scroll inicial;
* comportamiento mobile;
* fallback sin animación;
* posibilidad de WebGL, canvas, SVG o sólo DOM/CSS.

No diseñes el hero sin cerrar estas decisiones.

---

## Ronda 7: scroll y movimiento

Preguntame sobre:

* nivel general de movimiento;
* secciones con animación;
* scroll tradicional o narrativo;
* sticky sections;
* parallax;
* texto cinético;
* entrada de elementos;
* transiciones de página;
* microinteracciones;
* velocidad;
* reducción de movimiento;
* simplificación mobile.

---

## Ronda 8: navegador inteligente

Preguntame sobre:

* nivel de parecido con una app de mensajería;
* tono del asistente;
* avatar;
* burbujas;
* colores;
* ancho del chat;
* pantalla completa o embebido;
* cantidad visible de pasos;
* barra de progreso;
* escritura libre;
* botones;
* formularios;
* archivos;
* resultado final;
* animaciones;
* comportamiento mobile.

No inventes preguntas comerciales del flujo todavía, salvo que yo te lo pida.

---

## Ronda 9: componentes

Preguntame sobre:

* botones;
* enlaces;
* tarjetas;
* etiquetas;
* formularios;
* modales;
* paneles;
* acordeones;
* timeline;
* testimonios;
* logos;
* métricas;
* CTA;
* footer;
* estados.

Para cada componente importante, definí apariencia, uso y comportamiento.

---

## Ronda 10: clientes y soluciones

Preguntame sobre:

* formato del listado;
* jerarquía entre cliente y trabajo;
* uso de imágenes;
* logos;
* timeline;
* métricas;
* testimonios;
* tags;
* navegación entre clientes;
* visualización mobile;
* relación con el navegador inteligente.

---

## Ronda 11: imágenes e iconografía

Preguntame sobre:

* fotografía;
* capturas de producto;
* mockups;
* gráficos abstractos;
* ilustración;
* 3D;
* iconos;
* diagramas;
* tratamiento;
* consistencia;
* cuándo no usar imágenes.

---

## Ronda 12: responsive, accesibilidad y rendimiento

Preguntame sobre:

* breakpoints;
* prioridades mobile;
* simplificaciones;
* tamaños táctiles;
* navegación;
* contraste;
* teclado;
* lectores de pantalla;
* reducción de movimiento;
* carga;
* imágenes;
* video;
* WebGL;
* dispositivos de gama media o baja.

---

# Reglas para tus preguntas

No preguntes:

> ¿Qué colores te gustan?

Preguntá:

> ¿Qué base querés para la mayor parte del sitio?

Opciones de ejemplo:

* clara y luminosa;
* oscura e inmersiva;
* mixta según la sección;
* todavía no lo sé.

No preguntes:

> ¿Qué estilo querés?

Preguntá:

> ¿Qué debe dominar visualmente en la home?

Opciones de ejemplo:

* tipografía;
* gráficos abstractos;
* interfaces y datos;
* una combinación equilibrada.

No preguntes:

> ¿Qué animaciones te gustan?

Preguntá:

> ¿Dónde querés concentrar el movimiento?

Opciones de ejemplo:

* sólo en el hero;
* hero y transiciones;
* a lo largo de todo el recorrido;
* microinteracciones discretas.

Las opciones deben variar según el contexto real del proyecto.

---

# Validaciones visuales

Cuando una decisión sea difícil de resolver sólo con palabras, proponé una validación.

Ejemplos:

* tres moodboards;
* tres composiciones de hero;
* tres paletas;
* tres escalas tipográficas;
* dos comportamientos mobile;
* variantes de navegador inteligente.

No generes esas variantes sin pedirme autorización.

Marcá la decisión como:

> Requiere validación visual.

---

# Antes de generar el `design.md`

Cuando consideres que ya se resolvieron las decisiones principales, no generes el archivo directamente.

Primero entregame un resumen final llamado:

# Decisiones consolidadas

Debe incluir:

* personalidad;
* dirección visual;
* color;
* tipografía;
* grilla;
* responsive;
* hero;
* scroll;
* navegador inteligente;
* componentes;
* clientes;
* soluciones;
* imágenes;
* accesibilidad;
* rendimiento;
* pendientes menores.

Después preguntame explícitamente:

> ¿Confirmás estas decisiones para generar el `design.md`?

Sólo generá el archivo cuando yo confirme.

---

# Estructura del archivo final

Una vez confirmado, generá un único archivo Markdown con esta estructura:

# Socies Design System

## 1. Estado del documento

## 2. Objetivo del diseño

## 3. Contexto del producto

## 4. Principios

## 5. Personalidad de marca

## 6. Dirección visual

## 7. Sistema de color

## 8. Sistema tipográfico

## 9. Grilla y contenedores

## 10. Espaciado y ritmo

## 11. Responsive y mobile first

## 12. Header y navegación

## 13. Hero

## 14. Movimiento y scroll

## 15. Navegador inteligente

## 16. Componentes

## 17. Home

## 18. Landings de soluciones

## 19. Clientes y soluciones

## 20. Formularios y contacto

## 21. Footer

## 22. Imágenes, gráficos e iconografía

## 23. Estados y feedback

## 24. Accesibilidad

## 25. Rendimiento

## 26. Reglas editoriales

## 27. Tokens

## 28. Qué evitar

## 29. Checklist de implementación

## 30. Decisiones pendientes

Cada sección debe diferenciar claramente:

* reglas obligatorias;
* recomendaciones;
* excepciones;
* decisiones pendientes.

No conviertas recomendaciones en reglas cerradas.

No inventes contenido que yo no haya validado.

---

# Comenzá ahora

Empezá únicamente con:

1. una auditoría breve;
2. la lista de decisiones que todavía faltan;
3. la primera ronda de preguntas sobre personalidad de marca.

No generes todavía ninguna propuesta visual ni el archivo `design.md`.
