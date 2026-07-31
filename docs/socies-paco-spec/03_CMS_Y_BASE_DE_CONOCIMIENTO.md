# CMS y base de conocimiento

## 1. Principio general

El CMS es la fuente de verdad comercial. Los embeddings ayudan a encontrar contenido; no convierten texto libre en verdad verificable.

Separar siempre:

1. **descubrimiento semántico:** encontrar candidatos relacionados;
2. **recuperación exacta:** leer campos vigentes desde tablas normales;
3. **composición:** adaptar únicamente información permitida.

## 2. Entidades de negocio

### Servicios

Campos sugeridos:

- nombre;
- slug;
- resumen público;
- descripción;
- texto aprobado para Paco;
- problemas que resuelve;
- requisitos iniciales;
- modalidad: puntual, mensual, consultoría, pack;
- disponibilidad comercial: activa, pausada, privada;
- tecnologías relacionadas;
- rubros relacionados;
- trabajos relacionados;
- packs relacionados;
- preguntas de calificación sugeridas;
- campañas asociadas;
- prioridad;
- `chat_enabled`.

Cada servicio debe declarar además su regla de encaje:

```text
supported
conditional
unsupported
```

`conditional` incluye condiciones editoriales que el playbook puede verificar. `unsupported` utiliza una respuesta aprobada y, si existe, una alternativa real relacionada.

### Packs

- nombre;
- alcance incluido;
- exclusiones;
- precio y moneda;
- impuestos incluidos o no;
- vigencia desde/hasta;
- mercado o país;
- condiciones;
- audiencia ideal;
- texto aprobado;
- CTA;
- servicios incluidos;
- `price_is_public`;
- `chat_enabled`.

Paco solo muestra precio si el pack está activo, vigente, aplicable al mercado y marcado como público.

### Clientes

- nombre público;
- nombre interno;
- rubro;
- descripción breve;
- logo;
- relación con Socies;
- permiso de uso;
- texto aprobado para Paco;
- trabajos;
- testimonios;
- `chat_enabled`.

### Trabajos o proyectos

- título;
- cliente;
- problema documentado;
- solución documentada;
- alcance;
- resultado documentado;
- métricas verificadas;
- tecnologías;
- imágenes o video;
- URL pública;
- fechas;
- tiempo estimado solo si es reutilizable;
- texto aprobado para Paco;
- tags;
- permiso de uso;
- `chat_enabled`.

### Testimonios

- cita exacta;
- versión breve aprobada;
- autor;
- cargo;
- cliente;
- fuente o evidencia;
- permiso de uso;
- fecha;
- servicios y trabajos asociados;
- `chat_enabled`.

### Rubros y tecnologías

Deben ser entidades, no simples strings, para permitir:

- alias;
- taxonomía;
- relaciones;
- texto explicativo;
- búsqueda semántica;
- control editorial;
- métricas de uso.

### Reglas de encaje

El CMS debe permitir definir qué consultas atiende Socies y cuáles no:

```text
code estable
intent_code
status: supported|conditional|unsupported|unknown
conditions jsonb nullable
approved_response nullable
alternative_service_ids jsonb nullable
campaign_ids jsonb nullable
priority
version
```

Reglas:

- La ausencia de un trabajo publicado no equivale a `unsupported`.
- Solo una regla editorial explícita puede clasificar una intención como no ofrecida.
- El modelo puede proponer una intención; la aplicación resuelve el encaje.
- Las condiciones y alternativas se cargan por ID y se validan como cualquier contenido comercial.
- Un cambio de regla debe quedar versionado para auditar conversaciones anteriores.

## 3. Contenido específico del agente

### Intenciones

Ejemplos:

```text
landing_page
web_institucional
plataforma_a_medida
automatizacion
integracion
mantenimiento
servicio_mensual
consultoria
pack
empleo
proveedor
consulta_general
```

Las intenciones son editables. Deben conservar un código estable aunque cambie el nombre visible.

### Playbooks

Cada playbook define:

- intenciones aplicables;
- objetivo comercial;
- datos obligatorios;
- datos importantes;
- datos opcionales;
- preguntas candidatas;
- condiciones de pregunta;
- contenidos preferidos;
- reglas de cierre;
- cantidad máxima de turnos;
- clasificación posible.
- comportamiento para encaje soportado, condicional, no soportado o desconocido.

### Preguntas

Campos:

- código estable;
- pregunta aprobada;
- variante corta;
- componente UI;
- opciones;
- campo que completa;
- sensibilidad;
- posibilidad de omitir;
- condición de aparición;
- playbooks;
- campañas;
- prioridad;
- estado.

### Bloques de respuesta

Tipos sugeridos:

```text
acknowledgement
clarification
experience_intro
case_intro
testimonial_intro
contact_transition
qualification_transition
price_policy
time_policy
off_topic
closing
error
```

Campos:

- código;
- intención;
- etapa;
- texto aprobado;
- variables permitidas;
- adaptación permitida;
- campañas;
- prioridad;
- estado;
- versión.

Adaptación permitida:

```text
exact
shorten_only
tone_and_length
combine_approved_blocks
```

## 4. Embeddings

### Tabla lógica `knowledge_chunks`

```text
id
source_type
source_id
field_path
locale
plain_text
content_hash
embedding
embedding_model
embedding_dimensions
embedding_version
metadata jsonb
published_at
indexed_at
```

### Qué indexar

- resúmenes aprobados;
- problema y solución de trabajos;
- texto de Paco;
- descripción de servicios;
- audiencia y alcance de packs;
- testimonios breves;
- alias de rubros y tecnologías;
- preguntas frecuentes y objeciones.

### Qué no usar directamente como texto generativo

- instrucciones internas;
- notas privadas de clientes;
- datos personales;
- campos sin permiso de publicación;
- precios vencidos;
- borradores;
- contenido despublicado.

## 5. Estrategia de recuperación

### Paso 1 — Filtros

Aplicar antes o durante la búsqueda:

- `chat_enabled = true`;
- publicado;
- idioma;
- campaña;
- país o mercado;
- permiso de uso;
- vigencia;
- tipo de entidad.

### Paso 2 — Búsqueda semántica

Obtener un conjunto pequeño de candidatos, por ejemplo 10 a 20 chunks.

### Paso 3 — Agrupación y ranking

Agrupar por entidad y ponderar:

- similitud semántica;
- relación con intención;
- relación con rubro;
- prioridad editorial;
- campaña;
- diversidad;
- contenido ya mostrado;
- permiso comercial.

### Paso 4 — Recuperación exacta

Cargar la entidad completa por ID. El compositor recibe solo campos autorizados.

## 6. Índice vectorial

Para el volumen inicial, comenzar con búsqueda exacta o HNSW solo cuando sea necesario. `pgvector` soporta búsqueda exacta y los índices aproximados HNSW e IVFFlat. HNSW suele ofrecer una mejor relación velocidad/recall, con mayor costo de construcción y memoria.

No crear un índice aproximado por inercia. Medir primero:

- cantidad de chunks;
- latencia real;
- recall esperado;
- filtros frecuentes;
- memoria disponible.

## 7. Versionado y reindexación

- Cada cambio relevante recalcula `content_hash`.
- Una cola genera el embedding fuera del request del chat.
- Guardar modelo, dimensión y versión.
- Permitir reindexación por entidad, tipo o versión.
- No mezclar embeddings incompatibles en la misma consulta.
- Mantener el contenido anterior hasta que la nueva indexación sea válida.

## 8. Auditoría editorial

El CMS debe mostrar:

- dónde fue utilizado un contenido;
- en cuántas conversaciones apareció;
- última fecha de uso;
- versión usada;
- feedback o incidencia asociada;
- si Paco adaptó, combinó o copió el bloque.
