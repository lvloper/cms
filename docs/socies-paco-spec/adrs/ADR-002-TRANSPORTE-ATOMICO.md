# ADR-002 — Turnos JSON atómicos por HTTP

**Estado:** accepted  
**Fecha:** 2026-07-31

## Contexto

Cada respuesta combina texto breve, componentes y metadatos que deben validarse antes de mostrarse.

## Decisión

La API devuelve un turno JSON completo. No hay streaming token a token en el MVP.

## Alternativas descartadas

- Vercel AI SDK UI protocol.
- TanStack AI / AG-UI.
- Streaming parcial de JSON.
- Tool execution en el navegador.

Estas alternativas optimizan experiencias de asistencia abierta, pero agregan un protocolo que Paco no necesita.

## Consecuencias

- Validación completa antes de renderizar.
- TanStack Query administra mutaciones, errores y cache remoto.
- La percepción conversacional se logra con estados breves sobre datos ya validados.

## Reevaluar si

Las respuestas se vuelven largas, aparecen herramientas visibles o la latencia medida exige progreso incremental.
