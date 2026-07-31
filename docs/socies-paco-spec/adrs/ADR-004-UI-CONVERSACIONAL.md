# ADR-004 — Renderer propio sobre primitives accesibles

**Estado:** accepted  
**Fecha:** 2026-07-31

## Contexto

La UI necesita mensajería, formularios comerciales y cards de CMS con estética propia. El backend es fuente de verdad y devuelve una unión cerrada de componentes.

## Decisión

Construir un renderer específico con React, TanStack Query, React Hook Form, Zod, HTML nativo y Radix Primitives cuando exista comportamiento complejo.

Shadcn puede aportar componentes copiados y adaptados de forma selectiva después de verificar compatibilidad con React y Tailwind instalados.

## Alternativas descartadas

- assistant-ui runtime: duplica estado y abstracciones de thread.
- Vercel AI SDK UI y AI Elements completos: orientados a su protocolo y streaming.
- TanStack AI: duplica la orquestación del backend PHP.
- UI manual sin primitives: aumenta riesgo en foco, teclado y scroll.
- estética predeterminada de una librería: contradice el sistema visual canónico.

## Consecuencias

- Componentes auditables y adaptados a Socies.
- Más responsabilidad de pruebas de integración y accesibilidad.
- El modelo nunca selecciona nombres arbitrarios de componentes.

## Reevaluar si

Se incorporan múltiples threads, attachments, tool UI genérica o streaming complejo.
