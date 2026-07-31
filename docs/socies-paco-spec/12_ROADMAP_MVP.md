# Roadmap del MVP

## Fase 0 — Contratos y decisiones

Entregables:

- aprobar documentos funcionales;
- elegir proveedor/modelo inicial;
- confirmar versión de Laravel;
- definir schemas JSON;
- definir primeras intenciones y playbooks;
- definir campos de CMS mínimos;
- preparar conversaciones doradas.
- definir reglas iniciales `supported`, `conditional` y `unsupported` en CMS.
- aprobar contrato de campaña y precarga.
- implementar y probar `PacoBootstrapSeeder` según `17_SEEDS_INICIALES.md`.

Salida: contrato estable entre frontend, backend y CMS.

## Fase 1 — Vertical completa mínima

Primera vertical ejecutable: **landing page**. La arquitectura y el CMS deben ser genéricos desde el inicio para publicar después otros playbooks sin cambiar el renderer ni el pipeline.

Componentes:

- texto libre inicial;
- single select;
- formulario de contacto;
- texto final;
- una tarjeta de trabajo;
- un testimonio opcional.

Backend:

- crear conversación;
- guardar eventos;
- analizador estructurado;
- estado del lead;
- recuperación simple;
- compositor;
- validación;
- cierre `pending_review`;
- email interno.
- ruta propia y montaje inline en home;
- campaña y precarga segura;
- resolución de encaje desde CMS.

No implementar todavía scoring complejo ni embeddings si los contenidos pueden filtrarse por relaciones explícitas.

## Fase 2 — CMS y recuperación híbrida

- servicios;
- packs;
- clientes;
- trabajos;
- testimonios;
- rubros;
- tecnologías;
- preguntas;
- playbooks;
- bloques aprobados;
- `knowledge_chunks`;
- jobs de embeddings;
- búsqueda híbrida;
- permisos de publicación.
- reglas de encaje y respuestas para necesidades no ofrecidas.

## Fase 3 — Calificación dinámica

- selección de pregunta por utilidad;
- rol de decisión;
- empleados y facturación condicionales;
- scoring versionado;
- clasificación de consulta;
- próxima acción sugerida;
- resumen comercial estructurado.

## Fase 4 — Producción y seguridad

- Cloudflare rate limiting;
- Turnstile condicional;
- Laravel rate limits;
- locks e idempotencia;
- CORS por origen;
- CSP;
- políticas de retención;
- redacción de logs;
- dashboard de incidencias;
- fallback de proveedor.

## Fase 5 — Optimización

- campañas;
- contexto de páginas visitadas;
- ranking de contenido;
- A/B testing de preguntas;
- recuperación de conversación;
- multidioma;
- agenda;
- CRM;
- modelos alternativos;
- evaluación automática continua.

## Alcance recomendado del MVP

### Incluido

```text
texto
single select
multi select
contact form
cards de servicio/trabajo/pack
testimonio
persistencia progresiva
resumen + JSON
email al equipo
un máximo configurable de interacciones
```

### Fuera del MVP

```text
streaming token por token
toma humana en vivo
agenda automática
multidioma
upload de archivos
voz
respuestas abiertas ilimitadas
RAG sobre sitios externos
modelo con SQL libre
CRM externo
```

## Primeros playbooks sugeridos

1. Landing o sitio puntual.
2. Plataforma o desarrollo a medida.
3. Automatización o integración.
4. Servicio mensual o mantenimiento.
5. Pack publicado.
6. Consulta no comercial.

La fase 1 debe implementar uno solo y reutilizar la arquitectura para los demás.

Los playbooks que entran al MVP real se determinan por contenido aprobado en CMS. Cargar trabajos no define por sí solo la oferta: servicios y reglas de encaje indican qué hace Socies, qué evalúa condicionalmente y qué no ofrece.

## Criterios de aceptación de la vertical landing

Dado:

> Necesito desarrollar una landing page para una fundación ONG chica.

Paco debe:

- detectar `landing_page`;
- detectar ONG/fundación;
- guardar tamaño pequeño como dato relativo;
- guardar capacidad presupuestaria baja solo como inferencia débil;
- confirmar lo entendido brevemente;
- hacer una pregunta relevante;
- pedir contacto en el segundo momento configurado;
- mostrar contenido real relacionado si existe;
- preguntar como máximo dos datos adicionales;
- cerrar como `pending_review`;
- generar resumen y email;
- no informar precio ni tiempo no documentado.

## Orden de implementación técnico

1. DTOs y schemas.
2. Tablas de conversación y lead.
3. Modelos, relaciones y seeders de bootstrap.
4. API y fixtures sin IA.
5. Experiencia React con renderer de componentes.
6. Máquina de estado y respuestas fijas.
7. Gateway de modelo con fake provider.
8. Analizador real.
9. CMS mínimo y retrieval relacional.
10. Compositor real y validación.
11. Email y admin.
12. Embeddings.
13. Seguridad de producción.
14. Ampliación de playbooks desde CMS sin cambios de arquitectura.

Este orden permite probar el producto aunque el modelo todavía no esté conectado.
