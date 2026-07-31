# ADR-005 — CMS relacional y pgvector

**Estado:** accepted  
**Fecha:** 2026-07-31

## Contexto

Paco necesita cruzar similitud semántica con permisos, publicación, vigencia, encaje y relaciones editoriales.

## Decisión

PostgreSQL conserva datos de dominio y embeddings. El CMS es la fuente de verdad; pgvector ayuda a descubrir candidatos y nunca autoriza claims por sí solo.

## Alternativas descartadas

- Base vectorial externa en el MVP.
- RAG documental como fuente principal.
- Embeddings como único ranking.
- Índice aproximado creado antes de medir.

## Consecuencias

- Primera vertical puede usar relaciones y full-text sin embeddings.
- Recuperación exacta posterior a descubrimiento semántico.
- Modelo y dimensiones de embedding versionados.

## Reevaluar si

El corpus, distribución geográfica o latencia medida supera razonablemente la capacidad de PostgreSQL.
