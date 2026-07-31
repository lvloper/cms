# ADR-003 — Laravel AI SDK detrás de un gateway

**Estado:** accepted con spike pendiente  
**Fecha:** 2026-07-31

## Contexto

El proyecto usa Laravel 13 y necesita structured output, embeddings, fakes y posibilidad de cambiar de proveedor.

## Decisión

Usar `laravel/ai` detrás de `PacoModelGateway`. Dominio, DTOs y prompts no dependen de un proveedor concreto.

## Alternativas evaluadas

- Prism: plan de escape si el spike revela una limitación material.
- Neuron AI: no MVP; agrega runtime de agente y workflows innecesarios.
- LLPhant: orientado a RAG documental más genérico que el CMS relacional de Paco.
- `openai-php/client`: solo adapter específico si una capacidad aún no existe en el SDK elegido.

No instalar más de una abstracción principal en producción.

## Consecuencias

- El spike debe probar el mismo schema, prompts y evals con el proveedor elegido.
- Configuración de modelos por entorno.
- Fallback determinístico cuando el output no valida.

## Reevaluar si

Falla structured output, falta el proveedor necesario o aparece una diferencia demostrable de latencia, costo o confiabilidad.
