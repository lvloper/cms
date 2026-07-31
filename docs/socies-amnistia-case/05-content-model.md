# Modelo de contenido por bloques

Este archivo propone una estructura agnóstica al CMS. Puede adaptarse a Twill, Filament, un esquema JSON o un CMS propio.

---

## Metadatos del caso

```yaml
slug: amnistia-internacional-argentina
client: Amnistía Internacional Argentina
eyebrow: Cliente
title: Tecnología para acompañar la acción
summary: >
  Desde 2018 acompañamos a Amnistía Internacional Argentina como una
  extensión de su equipo digital, construyendo y sosteniendo las plataformas
  que utiliza para informar, movilizar y conectar con miles de personas.
relationship_since: 2018
industry:
  - Derechos humanos
  - Organización social
services:
  - Estrategia digital
  - Diseño UX/UI
  - Desarrollo
  - CMS
  - Automatizaciones
  - Integraciones
  - Infraestructura
  - Soporte continuo
featured: true
tone:
  - cercano
  - humano
  - profesional
```

---

## Bloques

```yaml
blocks:
  - type: hero_case
    variant: media_collage
    title: Tecnología para acompañar la acción
    summary: >
      Desde 2018 acompañamos a Amnistía Internacional Argentina como una
      extensión de su equipo digital.
    metrics:
      - label: Relación
        value: Desde 2018
      - label: Ecosistema
        value: 30+ propiedades digitales
        status: validate
      - label: Audiencia promedio
        value: 10.000 visitas únicas diarias
        status: validate
      - label: Operación
        value: 24/7
    media: []

  - type: marquee
    text:
      - Informar
      - Movilizar
      - Firmar
      - Donar
      - Participar
    speed: slow

  - type: text_media_grid
    eyebrow: Plataforma modular
    title: Un sistema para crear nuevas acciones
    body: >
      Diseñamos una plataforma modular para que el equipo pueda crear páginas,
      causas, campañas, acciones y formularios con autonomía.
    media: []

  - type: project_slider
    title: Diferentes causas, una plataforma flexible
    items:
      - title: Escribí por los Derechos
        summary: >
          Una experiencia de participación que reúne historias, peticiones y
          distintas formas de acción en un mismo recorrido.
        media: []
      - title: Derecho al aborto
        summary: >
          Un espacio digital capaz de organizar información compleja y ofrecer
          recorridos claros para distintos públicos.
        media: []

  - type: featured_project
    variant: cms_to_frontend
    title: Diario de Juicio
    body: >
      Un micrositio administrable y personalizable desde un CMS propio,
      preparado para adaptarse a diferentes campañas, casos y estructuras.
    media: []

  - type: statement
    eyebrow: Acompañamiento continuo
    title: Somos parte de su equipo
    body: >
      Conocemos el ecosistema, entendemos las prioridades y trabajamos junto
      al equipo para resolver necesidades de diseño, desarrollo,
      infraestructura y operación digital.
    media: []

  - type: process_map
    title: Lo que ocurre detrás de cada campaña
    nodes:
      - Formularios
      - Salesforce
      - Automatizaciones
      - Email
      - Certificados
      - Infraestructura
      - Backups
      - Monitoreo

  - type: metrics_media
    title: Preparados para responder
    body: >
      La infraestructura fue creciendo junto con la organización. La
      adaptamos, monitoreamos y escalamos para sostener el tráfico cotidiano
      y responder ante campañas de alta demanda.
    metrics:
      - value: 30+
        label: sitios, micrositios y formularios
        status: validate
      - value: ~10.000
        label: visitas únicas diarias
        status: validate
      - value: 24/7
        label: monitoreo y operación
    media: []

  - type: testimonial
    person: Laura Durán
    role: Directora de Comunicación y Prensa
    quote: >
      Cada vez que surgió una necesidad urgente, respondieron con rapidez y
      priorizando nuestras solicitudes.
    full_quote_ref: copy-laura
    image: null

  - type: media_wall
    title: Una relación que continúa creciendo
    media: []

  - type: case_cta
    title: Conocé cómo podemos integrarnos a tu equipo
    action:
      label: Hablemos
      href: /contacto
```

---

## Tipos de bloque necesarios

### `hero_case`

Hero editorial para cliente con múltiples trabajos.

### `marquee`

Pausa de ritmo con conceptos o acciones.

### `text_media_grid`

Texto corto acompañado por una grilla asimétrica.

### `project_slider`

Dos o tres experiencias representativas.

### `featured_project`

Proyecto con mayor profundidad, sin convertirse en otro caso completo.

### `statement`

Bloque tipográfico y humano.

### `process_map`

Diagrama de integraciones, procesos o ecosistema.

### `metrics_media`

Métricas acompañadas por video, captura o animación.

### `testimonial`

Testimonio individual.

### `media_wall`

Cierre visual con piezas variadas.

### `case_cta`

Llamado a la acción final.

---

## Reglas de validación

- Máximo de 100 palabras por bloque.
- Máximo de tres proyectos en `project_slider`.
- Un solo `marquee` por caso.
- Uno o dos testimonios como máximo.
- Todos los números públicos deben tener fuente interna.
- Cada bloque debe contener al menos una pieza visual, excepto `marquee`.
- El caso debe funcionar aunque se eliminen uno o dos bloques opcionales.
