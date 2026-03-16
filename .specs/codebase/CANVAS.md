# Canvas — Arquitetura JS

**Analisado em:** 2026-03-16

O canvas interativo do CraftDB é composto por 10 arquivos JS em `resources/js/canvas/`. Combina HTML5 Canvas 2D (grid/pan/zoom) com Alpine.js stores (estado das entidades) e D3.js (grafos de relacionamentos).

---

## Visão geral

```
resources/js/canvas/
├── main.js                          # Entry point: registra Alpine data stores
├── helpers.js                       # Utilitários compartilhados
├── classes/
│   └── InfiniteCanvas.js            # Motor do canvas HTML5 (pan/zoom/touch/grid)
└── data/
    ├── _canvas.data.js              # runCanvas — coordenador principal
    ├── _table.data.js               # runTable — posição/drag/resize de tabelas
    ├── _area.data.js                # runArea — posição/drag/resize de áreas
    ├── _note.data.js                # runNote — posição/drag/resize de notas
    ├── _svg-lines.js                # runSvgLines — linhas SVG de relacionamentos
    ├── _foreign-keys-graph.js       # foreignKeysGraph — grafo D3 de FK por tabela
    └── _diagram-graph.js            # diagramGraph — grafo D3 completo do diagrama
```

---

## InfiniteCanvas.js

**Localização:** `resources/js/canvas/classes/InfiniteCanvas.js`
**Propósito:** Motor de renderização do grid com pan/zoom/touch

**Responsabilidades:**
- Renderiza grid adaptativo no `<canvas id="canvas">` via HTML5 Canvas 2D
- Gerencia escala (`#scale`) e offsets (`#offsetX`, `#offsetY`) como campos privados
- Pan via mouse: hold Space + drag, ou modo grab permanente
- Pan via touch: dois dedos (pinch = zoom, dois dedos = pan)
- **Conversão de coordenadas:** `toVirtualX/Y()` (real → virtual) e `toRealX/Y()` (virtual → real)
- Expõe `getTransformString()` para sincronizar a camada SVG de relacionamentos

**Eventos emitidos:**
- `canvas-zoom` — ao alterar escala (detail: `{ scale }`)
- `canvas::space` — ao pressionar/soltar Space (detail: `{ isSpacePressed }`)
- `canvas::transform` — ao realizar pan (detail: `{ transform }`)

---

## Alpine.js Stores

### _canvas.data.js → `runCanvas`

**Propósito:** Coordenador principal do canvas — instancia `InfiniteCanvas` e orquestra todos os modos de interação

**Estado:**
- `canvas` — instância de `InfiniteCanvas`
- `zoomPercent` — porcentagem de zoom exibida na UI
- `isInRelationshipEvent`, `isInAddTableEvent`, `isInAddAreaEvent`, `isInAddNoteEvent` — modos de adição ativos
- `relationshipEvent` — dados de FK em criação (`from_table_id`, `to_table_id`, `type`)

**Dependências externas:** axios (salvar posições), canvas-confetti (animação no primeiro load)

---

### _table.data.js → `runTable`

**Propósito:** Gerencia posição, drag e resize de cada tabela no canvas

**Estado:**
- `x`, `y` — posição atual (snap ao grid de 25px)
- `zIndex` — z-index dinâmico; contador global `window.tableZIndexCounter` inicia em **1000**
- `isDragging`, `isResizing` — flags de interação ativa
- `expanded` — tabela expandida/colapsada
- `colWidths` — larguras das colunas da tabela: `[65, 30, 5]` (name, type, button)
- `visibleColumnsCount`, `hiddenColumnsCount` — calculados dinamicamente via resize

**Eventos escutados:** `canvas::space` (desativa drag quando space pressionado)
**Eventos emitidos:** `table::move` (durante drag), `table::expanded` (ao expandir/colapsar)

---

### _area.data.js → `runArea`

**Propósito:** Gerencia posição, drag e resize de áreas (grupos visuais no canvas)

**Estado:** `x`, `y`, `width`, `height`, `zIndex`
**Z-index global:** `window.areaZIndexCounter` inicia em **500** (abaixo de tabelas e notas)

---

### _note.data.js → `runNote`

**Propósito:** Gerencia posição, drag e resize de notas (sticky notes no canvas)

**Estado:** `x`, `y`, `width`, `height`, `zIndex`
**Z-index global:** `window.noteZIndexCounter` inicia em **600** (acima de áreas, abaixo de tabelas)

---

### _svg-lines.js → `runSvgLines`

**Propósito:** Renderiza e atualiza linhas SVG que representam os relacionamentos entre tabelas

**Caches de performance (críticos):**
- `svgElementCache` (Map) — elementos SVG por connection ID
- `domElementCache` (Map) — elementos DOM de colunas/tabelas
- `boundingRectCache` (Map) — `getBoundingClientRect()` por elemento; invalidado em zoom/transform/drag
- `anchorCache` (Map) — elementos `.anchor-left`/`.anchor-right`
- `transformCache` — `gCTM.inverse()` reutilizado entre renders
- `rafId` — RequestAnimationFrame ID para debounce de recompute

**Eventos escutados:**
- `canvas-zoom` → aplica transform no `<g>` SVG e invalida boundingRectCache
- `canvas::transform` → aplica transform e invalida cache
- `table::move` → debounced recompute das linhas
- `table::expanded` → recomputa linhas
- `relationships::refresh` → recomputa linhas após mudança de dados

---

### _foreign-keys-graph.js → `foreignKeysGraph`

**Propósito:** Grafo D3.js de força mostrando os relacionamentos de FK de **uma tabela específica** (exibido no painel lateral)

**Tecnologia:** D3.js force simulation
**Inicialização:** lazy — aguarda o tab "foreign-keys" estar visível e o elemento ter dimensões

---

### _diagram-graph.js (sem nome Alpine registrado)

**Propósito:** Grafo D3.js de força mostrando o **diagrama completo** (visão macro de todas as tabelas e relacionamentos)

**Tecnologia:** D3.js force simulation
**Inicialização:** lazy com delay de 300ms para garantir que o modal está visível

---

## main.js — Registro dos Stores

```js
// Registra todos os Alpine data components no evento alpine:init
Alpine.data('runTable', _table);
Alpine.data('runArea', _area);
Alpine.data('runNote', _note);
Alpine.data('runCanvas', _canvas);
Alpine.data('runSvgLines', _svgLines);
Alpine.data('foreignKeysGraph', _foreignKeysGraph);
// _diagram-graph.js é importado mas não registrado via Alpine.data (uso direto)
```

---

## Hierarquia de Z-index

| Elemento | Contador global | Valor inicial |
|---|---|---|
| Áreas | `window.areaZIndexCounter` | 500 |
| Notas | `window.noteZIndexCounter` | 600 |
| Tabelas | `window.tableZIndexCounter` | 1000 |

Cada vez que o usuário clica/move um elemento, seu z-index é incrementado do contador global para ficar por cima dos demais.

---

## Padrões de Performance

1. **BoundingRect cache** — `getBoundingClientRect()` é chamado uma vez e cacheado; invalidado apenas em zoom, pan ou drag
2. **SVG debounce + RAF** — recompute de linhas é agendado via `setTimeout` + `requestAnimationFrame` para evitar thrashing de layout
3. **Transform em `<g>` SVG** — ao invés de recomputar paths em cada zoom/pan, aplica-se o transform do `InfiniteCanvas` direto no `<g>` container das linhas
4. **Lazy D3 init** — grafos D3 são inicializados apenas quando o container está visível e com dimensões definidas

---

## Eventos Custom (contrato entre stores)

| Evento | Emitido por | Escutado por | Payload |
|---|---|---|---|
| `canvas-zoom` | `InfiniteCanvas` | `_svg-lines`, `_table` | `{ scale }` |
| `canvas::space` | `InfiniteCanvas` | `_table`, `_area`, `_note` | `{ isSpacePressed }` |
| `canvas::transform` | `InfiniteCanvas` | `_svg-lines` | `{ transform }` |
| `table::move` | `_table.data` | `_svg-lines` | `{ tableId }` |
| `table::expanded` | `_table.data` | `_svg-lines` | — |
| `relationships::refresh` | Livewire (server) | `_svg-lines` | — |
| `table::adding` | Livewire | `_canvas.data` | — |
| `area::adding` | Livewire | `_canvas.data` | — |
| `note::adding` | Livewire | `_canvas.data` | — |
| `save-positions` | Livewire | `_canvas.data` | — |
