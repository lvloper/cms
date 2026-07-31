# Librerías y decisiones técnicas

## 1. Criterio

Este documento contiene únicamente el stack elegido y las condiciones de incorporación. Las alternativas, riesgos y motivos de descarte viven en `adrs/`.

No instalar dependencias hasta que una fase de implementación las necesite. Usar versiones compatibles con los lockfiles del proyecto.

## 2. Stack existente confirmado

```text
PHP 8.3+
Laravel 13
Filament 5
Inertia 3
React 19
Tailwind CSS 3
Vite 8
PostgreSQL
```

La spec no debe seguir tratando estas versiones como decisiones abiertas.

## 3. Backend e IA

### Elegido

```text
laravel/ai detrás de PacoModelGateway
PostgreSQL + pgvector
Redis para cache, locks, rate limits y colas
Laravel Queue / Horizon cuando se habilite Redis en producción
```

Reglas:

- El dominio no depende directamente de clases del proveedor.
- Structured output es obligatorio para analizador y compositor.
- Embeddings, proveedor y modelo se configuran por entorno.
- No instalar Laravel AI SDK y otro framework de agentes como arquitecturas paralelas.
- El modelo no recibe SQL, escritura al CMS ni herramientas HTTP abiertas.

Referencia: `adrs/ADR-003-INTEGRACION-DE-MODELOS.md`.

## 4. Frontend conversacional

### Elegido

```text
React + TypeScript
TanStack Query para estado remoto y mutaciones
useReducer o Context para estado visual local
Zod para validación defensiva del contrato
React Hook Form para formularios compuestos
Radix Primitives para comportamientos complejos
HTML nativo cuando sea suficiente
libphonenumber-js para ayuda de normalización en cliente
react-textarea-autosize para campos abiertos
```

Shadcn puede incorporarse de forma selectiva como código editable, siempre que el componente sea compatible con React y Tailwind instalados. No se adopta su estética por defecto ni un registry completo.

La apariencia proviene exclusivamente de `docs/ux/design-system.md`.

Referencia: `adrs/ADR-004-UI-CONVERSACIONAL.md`.

## 5. Transporte y estado

- API HTTP con turnos JSON atómicos.
- TanStack Query administra pending, error, reintentos seguros y actualización del cache remoto.
- PostgreSQL es la fuente de verdad de la conversación.
- El cache del navegador no contiene transcript, nombre, email ni teléfono.
- Toda mutación usa `Idempotency-Key` y `conversation_version`.
- No usar streaming token a token en el MVP.

Referencia: `adrs/ADR-002-TRANSPORTE-ATOMICO.md`.

## 6. Contratos PHP y TypeScript

Adoptar cuando se estabilice el primer contrato:

```text
spatie/laravel-data
spatie/laravel-typescript-transformer
dedoc/scramble como documentación OpenAPI de desarrollo
```

- DTOs compartidos generan tipos, pero no reemplazan entidades de dominio.
- Zod valida en el límite del frontend.
- Laravel valida nuevamente en servidor.
- Fixtures de contrato se ejecutan en backend y frontend.

## 7. Búsqueda y conocimiento

- Filtros relacionales y permisos antes de generar claims.
- Full-text y relaciones editoriales para la primera vertical.
- `pgvector` al incorporar recuperación semántica.
- Sin base vectorial externa en el MVP.
- Índice aproximado solo después de medir volumen, latencia y recall.

Referencia: `adrs/ADR-005-CONOCIMIENTO-Y-PGVECTOR.md`.

## 8. Testing

```text
PHPUnit 12 existente
Laravel AI fakes
Vitest
React Testing Library
Playwright
fixtures y evals propios de Paco
```

No incorporar un segundo runner PHP sin una migración explícita del proyecto.

## 9. Paquetes que no se adoptan en el MVP

```text
framework completo de chatbot
assistant-ui runtime
Vercel AI SDK UI runtime
TanStack AI
XState para duplicar la máquina de estados backend
Zustand para el mismo estado remoto de TanStack Query
SDK del proveedor de IA en el navegador
framework multiagente
vector database externa
Markdown con HTML habilitado
```

Los motivos y condiciones de reevaluación están en los ADRs.

## 10. Instalación por fase

### Vertical sin modelo real

- DTOs y schemas manuales mínimos.
- Fixtures de API.
- Renderer React.
- Máquina de estados y proveedor fake.

### Integración del agente

- `laravel/ai`.
- gateway y fakes.
- proveedor/modelo configurados.

### UI interactiva

- TanStack Query.
- Zod.
- React Hook Form.
- primitives Radix únicamente donde sean necesarios.

### Recuperación semántica

- pgvector.
- jobs de embeddings.
- evals de retrieval.

### Producción

- Redis/Horizon.
- Turnstile.
- observabilidad y políticas de retención.

## 11. Fuentes oficiales

- Laravel AI SDK 13: https://laravel.com/docs/13.x/ai-sdk
- Radix Primitives: https://www.radix-ui.com/primitives/docs/overview/introduction
- TanStack Query: https://tanstack.com/query/latest/docs/framework/react/overview
- React Hook Form: https://react-hook-form.com/
- Zod: https://zod.dev/
- pgvector: https://github.com/pgvector/pgvector
- Vite library mode: https://vite.dev/guide/build
- shadcn/ui: https://ui.shadcn.com/

## 12. ADRs relacionados

- `ADR-001-PIPELINE-CONTROLADO.md`
- `ADR-002-TRANSPORTE-ATOMICO.md`
- `ADR-003-INTEGRACION-DE-MODELOS.md`
- `ADR-004-UI-CONVERSACIONAL.md`
- `ADR-005-CONOCIMIENTO-Y-PGVECTOR.md`
