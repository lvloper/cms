# ADR-001 — Pipeline controlado por Laravel

**Estado:** accepted  
**Fecha:** 2026-07-31

## Contexto

Paco tiene un objetivo comercial acotado, un número máximo de interacciones y reglas estrictas sobre claims, precios, tiempos y componentes de UI.

## Decisión

Laravel controla un pipeline finito por turno. El modelo analiza o compone dentro de schemas cerrados; no ejecuta un loop autónomo.

## Alternativas descartadas

- Framework multiagente: duplica la máquina de estados y agrega autonomía innecesaria.
- Agent loop con tools generales: dificulta límites, auditoría e idempotencia.
- Prompt monolítico que decide y ejecuta todo: reduce trazabilidad y control de claims.

## Consecuencias

- Mayor código de aplicación, pero comportamiento auditable.
- Tools internas con parámetros cerrados.
- Dos llamadas de modelo como máximo y respuestas fijas cuando existe una política exacta.

## Reevaluar si

Existen workflows largos, aprobaciones humanas dentro del flujo o múltiples agentes realmente independientes.
