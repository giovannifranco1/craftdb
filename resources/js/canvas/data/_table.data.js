// Contador global para z-index (inicia em 1000)
if (typeof window.tableZIndexCounter === 'undefined') {
    window.tableZIndexCounter = 1000;
}

// @Alpine.js data
export default (x, y, tableId) => ({
    x: x,
    y: y,
    tableId: tableId,
    zIndex: null, // z-index será definido quando necessário
    isDragging: false,
    isResizing: false,
    resizeDirection: null,
    startX: 0,
    startY: 0,
    initialX: 0,
    initialY: 0,
    initialHeight: 0,
    width: 300, // valor inicial (suave para grid 25px)
    height: null, // altura inicial será calculada
    maxHeight: null, // altura máxima inicial (pode ser aumentada via resize)
    maxResizeHeightDown: null,
    maxResizeHeightUp: null,
    resizeStartX: 0,
    initialWidth: 0,
    colWidths: [70, 30], // Column resize state
    expanded: true,
    visibleColumnsCount: 0,
    hiddenColumnsCount: 0,
    previousVisibleColumnsCount: 0, // Para rastrear mudanças durante resize


    init() {
        // Snap inicial ao grid (caso valores venham desalinhados do backend)
        this.x = this.snapToGrid(this.x);
        this.y = this.snapToGrid(this.y);

        this.$el.style.transform = `translate3d(${this.x}px, ${this.y}px, 0)`;
        this.$el.style.left = '0px';
        this.$el.style.top = '0px';

        const headerHeight = this.getGridSize() / 2 + this.getGridSize();
        const footerHeight = this.getGridSize() / 2 + this.getGridSize();
        const rowHeight = this.getGridSize();
        const maxColumns = this.$wire.tableData.resize_column_position;
        this.maxHeight = headerHeight + (maxColumns * rowHeight) + footerHeight;
        this.maxResizeHeightDown = this.maxHeight + (this.$wire.tableData.columns_count * rowHeight);
        this.maxResizeHeightUp = (this.maxHeight + rowHeight);

        // Define altura inicial mínima (header + footer) enquanto calcula
        this.height = headerHeight + footerHeight;

        // Usa $nextTick para garantir que o DOM está renderizado
        this.$nextTick(() => {
            this.height = this.calculateInitialHeight();
            this.updateVisibleColumnsCount();
        });

        this.canDragBySpace = true;

        window.addEventListener('canvas::space', (e) => {
            this.canDragBySpace = !e.detail.isSpacePressed;
            this.updateCursorBySpace();
        });

        this.updateCursorBySpace = () => {
            if (!this.canDragBySpace) {
                this.$el.style.cursor = 'grab';
            } else {
                this.$el.style.cursor = '';
            }
        };

        this.updateCursorBySpace();

        this.$el.addEventListener('mousedown', (e) => {
            const isResizeHandle = e.target.classList.contains('resize-handle') ||
                                   e.target.closest('.resize-handle') !== null;
            if (!isResizeHandle && !this.isResizing) {
                this.startDrag(e);
            }
        });

        this.$refs.canvasRoot.addEventListener('mousemove', (e) => {
            if (this.isResizing) {
                this.resize(e);
            } else if (this.isDragging) {
                this.drag(e);
            }
        });

        this.$refs.canvasRoot.addEventListener('mouseup', (e) => {
            this.endDrag(e);
            this.endResize();
        });

        this.$refs.canvasRoot.addEventListener('mouseleave', (e) => {
            this.endDrag(e);
            this.endResize();
        });

        this.$root.addEventListener('dropdown-menu::opened', () => this.setHighestZIndex());

        // Observa mudanças na altura para atualizar contagem de colunas
        this.$watch('height', () => this.updateVisibleColumnsCount());
        this.$watch('expanded', () => {
            setTimeout(() => {
                this.recalculateHeightIfNeeded();
            }, 100);
        });

        // Observa quando as colunas são carregadas para recalcular altura
        this.$el.addEventListener('table::columns-loaded', () => {
            setTimeout(() => {
                this.recalculateHeightIfNeeded();
            }, 100);
        });

        // Observa eventos de mudança de colunas
        window.addEventListener(`table::column-added`, () => {
            setTimeout(() => this.recalculateHeightIfNeeded(), 100);
        });

        window.addEventListener(`relationships::fresh`, () => {
            setTimeout(() => this.recalculateHeightIfNeeded(), 100);
        });

        window.addEventListener(`table::column-saved`, () => {
            setTimeout(() => this.recalculateHeightIfNeeded(), 100);
        });

        // Observa mudanças no DOM quando colunas são adicionadas/removidas
        const observer = new MutationObserver(() => {
            if (this.expanded) {
                setTimeout(() => this.recalculateHeightIfNeeded(), 100);
            }
        });

        // Observa mudanças no tbody quando as colunas são carregadas
        this.$nextTick(() => {
            const tbody = this.$el.querySelector('table tbody');
            if (tbody) {
                observer.observe(tbody, { childList: true, subtree: false });
            }

            // Também observa o container da estrutura da tabela
            const structureContainer = this.$el.querySelector('[x-show*="expanded"]');
            if (structureContainer) {
                observer.observe(structureContainer, { childList: true, subtree: true });
            }
        });

        // Listener para quando Livewire atualiza
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', ({ el }) => {
                if (el.closest && el.closest(`[table-id="${this.tableId}"]`)) {
                    setTimeout(() => this.recalculateHeightIfNeeded(), 100);
                }
            });
        });
    },

    getScale() {
        return Alpine.raw(this.canvas).getScale();
    },
    getGridSize() {
        return Alpine.raw(this.canvas).cellSize || 40;
    },
    getSnappedWidth() {
        const grid = this.getGridSize();
        return Math.max(grid, Math.round(this.width / grid) * grid);
    },
    snapToGrid(coord) {
        const gridSize = this.getGridSize();
        return Math.round(coord / gridSize) * gridSize;
    },
    setHighestZIndex() {
        window.tableZIndexCounter += 1;
        const newZIndex = window.tableZIndexCounter;
        this.zIndex = newZIndex;
        this.$el.style.zIndex = newZIndex;

        return newZIndex;
    },
    startDrag(e) {
        if (!this.canDragBySpace) return; // bloqueia o drag se espaço está pressionado

        this.isDragging = true;
        this.startX = e.clientX;
        this.startY = e.clientY;
        this.initialX = this.x;
        this.initialY = this.y;
        this.$el.style.transition = 'none';

        this.setHighestZIndex();
    },
    drag(e) {
        if (!this.isDragging) return;
        e.preventDefault();

        const scale = this.getScale();
        const dx = (e.clientX - this.startX) / scale;
        const dy = (e.clientY - this.startY) / scale;

        // Calcula nova posicao "virtual" prevista
        let nextX = this.initialX + dx;
        let nextY = this.initialY + dy;

        // Snap robusto ao grid
        const snappedX = this.snapToGrid(nextX);
        const snappedY = this.snapToGrid(nextY);

        // Debug: Mostrar valores no console para diagnóstico
        if (window?.DEBUG_TABLE_DRAG) {
            console.log('[TableDrag] nextX:', nextX, 'nextY:', nextY, '=> snappedX:', snappedX, 'snappedY:', snappedY);
        }

        this.x = snappedX;
        this.y = snappedY;

        // Aplica sempre transform (sem left/top)
        this.$el.style.left = '0px';
        this.$el.style.top = '0px';
        this.$el.style.transform = `translate3d(${this.x}px, ${this.y}px, 0)`;
        this.$dispatch('table::move', { tableId: this.tableId });
    },
    endDrag() {
        if (!this.isDragging) return;
        this.isDragging = false;
        // Mantém o z-index alto (não reseta, para que a tabela permaneça sobre as outras)
        this.$dispatch(`table::move.${this.tableId}`, { x: this.x, y: this.y });
    },
    toggleExpanded(element) {
        this.expanded = !this.expanded;
        const tableEl = element.querySelector('table');

        if (tableEl) tableEl.toggleAttribute('is-collapsed', !this.expanded);

        setTimeout(() => {
            this.$dispatch('table::expanded');
            this.updateVisibleColumnsCount();
        }, 100);
    },

    calculateInitialHeight() {
        // Calcula altura inicial baseada no conteúdo, limitada a no máximo 7 colunas
        const headerHeight = this.getGridSize() / 2 + this.getGridSize();
        const footerHeight = 37.5; // altura aproximada do footer
        const rowHeight = this.getGridSize();
        const maxColumns = this.$wire.tableData.resize_column_position;
        const tableEl = this.$el.querySelector('table tbody');

        let calculatedHeight = headerHeight + footerHeight;

        if (tableEl && this.expanded) {
            const rows = Array.from(tableEl.querySelectorAll('tr')).filter(row =>
                !row.querySelector('td[colspan="3"]')
            );

            const totalColumns = rows.length;

            if (totalColumns > 0) {
                const visibleColumns = Math.min(totalColumns, maxColumns);
                const contentHeight = visibleColumns * rowHeight;

                // Se há mais colunas do que o máximo visível, precisa adicionar espaço para a linha "more ..."
                const moreRowHeight = totalColumns > maxColumns ? rowHeight : 0;

                calculatedHeight = headerHeight + contentHeight + moreRowHeight + footerHeight;
            }
        }

        return calculatedHeight;
    },

    recalculateHeightIfNeeded() {
        // Recalcula apenas se a altura não foi aumentada via resize
        this.maxResizeHeightDown = this.maxHeight + (this.$wire.tableData.columns_count * this.getGridSize());
        this.maxResizeHeightUp = this.maxHeight + this.getGridSize();
        this.maxColumns = this.$wire.tableData.resize_column_position;
        const headerHeight = this.getGridSize() / 2 + this.getGridSize();
        const footerHeight = this.getGridSize() / 2 + this.getGridSize();
        const rowHeight = this.getGridSize();
        this.maxHeight = headerHeight + (this.maxColumns * rowHeight) + footerHeight;

        if (this.height <= this.maxHeight) {
            const newHeight = this.calculateInitialHeight();
            this.height = newHeight;
        }

        this.updateVisibleColumnsCount();
    },

    updateVisibleColumnsCount() {
        if (!this.expanded) {
            this.visibleColumnsCount = 0;
            this.hiddenColumnsCount = 0;
            return;
        }

        const tableEl = this.$el.querySelector('table tbody');
        if (!tableEl) {
            this.visibleColumnsCount = 0;
            this.hiddenColumnsCount = 0;
            return;
        }

        const rows = Array.from(tableEl.querySelectorAll('tr')).filter(row =>
            !row.querySelector('td[colspan="3"]')
        );

        const headerHeight = this.getGridSize() / 2 + this.getGridSize();
        const footerHeight = this.getGridSize() / 2 + this.getGridSize();
        const rowHeight = this.getGridSize();
        const availableHeight = this.height - headerHeight - footerHeight;
        let visibleRows = Math.floor(availableHeight / rowHeight);

        if (visibleRows >= rows.length) {
            this.visibleColumnsCount = rows.length;
            this.hiddenColumnsCount = 0;
        } else {
            visibleRows = Math.max(1, visibleRows - 1);
            this.visibleColumnsCount = visibleRows;
            this.hiddenColumnsCount = rows.length - visibleRows;
        }
    },

    startResize(direction, e) {
        e.preventDefault();
        e.stopPropagation();

        // Prevent drag from starting
        this.isDragging = false;

        this.isResizing = true;
        this.resizeDirection = direction;
        this.startX = e.clientX;
        this.startY = e.clientY;
        this.initialHeight = this.height;
        this.$el.style.transition = 'none';

        this.setHighestZIndex();
        document.body.style.userSelect = 'none';
    },

    resize(e) {
        if (!this.isResizing) return;
        e.preventDefault();
        e.stopPropagation();

        const scale = this.getScale();
        const dy = (e.clientY - this.startY) / scale;

        let newHeight = this.initialHeight;

        if (this.resizeDirection === 'bottom') {
            // Permite aumentar ou diminuir, mas respeitando os limites mínimo e máximo
            const headerHeight = this.getGridSize() / 2 + this.getGridSize();
            const footerHeight = 25;
            const minAllowedHeight = headerHeight + footerHeight;
            const calculatedHeight = this.initialHeight + dy;

            // Verifica se o resize deve ser bloqueado
            const rowHeight = this.getGridSize();
            const availableHeight = calculatedHeight - headerHeight - footerHeight;
            const visibleRows = Math.max(1, Math.floor(availableHeight / rowHeight) - 1);
            const totalHeightCurrent = this.maxHeight + (visibleRows * rowHeight);

            // Bloqueia o resize se tentar aumentar além do máximo permitido
            if (dy > 0 && totalHeightCurrent >= this.maxResizeHeightDown) {
                return;
            }

            if (dy < 0 && totalHeightCurrent <= this.maxResizeHeightUp) {
                return;
            }

            // Limita entre o mínimo e o máximo permitido
            // Permite diminuir livremente, mas bloqueia aumentar além do maxResizeHeight
            newHeight = Math.max(minAllowedHeight, Math.min(this.maxResizeHeightDown, calculatedHeight));
        }

        // Snap to grid
        this.height = this.snapToGrid(newHeight);

        // Update element styles directly
        this.$el.style.height = `${this.height}px`;
        this.$el.style.maxHeight = `${this.height}px`;

        // Atualiza contagem de colunas visíveis durante o resize
        const previousCount = this.visibleColumnsCount;
        this.updateVisibleColumnsCount();

        // Verifica se alguma coluna com relacionamento mudou de estado
        // Usa $nextTick para esperar o Alpine processar as mudanças no DOM
        if (previousCount !== this.visibleColumnsCount) {
            this.$nextTick(() => {
                this.checkAndDispatchRelationshipRefresh(previousCount);
            });
        }
    },

    checkAndDispatchRelationshipRefresh(previousVisibleCount) {
        // Verifica se alguma coluna que mudou de estado (hidden/visible) tem relacionamento
        const tableEl = this.$el.querySelector('table tbody');
        if (!tableEl) return;

        const rows = Array.from(tableEl.querySelectorAll('tr')).filter(row =>
            !row.querySelector('td[colspan="3"]')
        );

        // Determina o range de colunas que mudou
        const minIndex = Math.min(previousVisibleCount, this.visibleColumnsCount);
        const maxIndex = Math.max(previousVisibleCount, this.visibleColumnsCount);

        // Verifica se alguma coluna no range que mudou tem relacionamento
        // Verifica tanto pelo atributo is-hidden quanto pela condição do x-show
        let hasRelationshipInChangedRange = false;

        for (let i = minIndex; i < maxIndex && i < rows.length; i++) {
            const row = rows[i];
            const columnId = row.getAttribute('id');

            if (!columnId) continue;
            if (!this.columnHasRelationship(columnId)) continue;

            hasRelationshipInChangedRange = true;
            break;
        }

        // Dispara evento apenas se houver coluna com relacionamento que mudou
        if (hasRelationshipInChangedRange) {
            this.$dispatch('relationships::refresh');
        }
    },

    columnHasRelationship(columnId) {
        const columnEl = document.getElementById(columnId);
        if (!columnEl) return false;

        // Usa o atributo has-relationship que foi adicionado no template
        const hasRelationshipFrom = columnEl.getAttribute('has-relationship-from');
        const hasRelationshipTo = columnEl.getAttribute('has-relationship-to');
        const hasRelationship = hasRelationshipFrom === 'true' || hasRelationshipTo === 'true';
        return hasRelationship;
    },

    endResize() {
        if (!this.isResizing) return;

        const savedHeight = this.height;
        const previousCount = this.visibleColumnsCount;
        this.isResizing = false;
        this.resizeDirection = null;

        document.body.style.userSelect = '';

        this.$el.style.height = `${savedHeight}px`;
        this.$el.style.maxHeight = `${savedHeight}px`;

        this.updateVisibleColumnsCount();

        // Verifica se alguma coluna com relacionamento mudou de estado no final do resize
        // Usa $nextTick para esperar o Alpine processar as mudanças no DOM
        if (previousCount !== this.visibleColumnsCount) {
            this.$nextTick(() => {
                this.checkAndDispatchRelationshipRefresh(previousCount);
            });
        }

        this.$dispatch(`table::resize.${this.tableId}`, { visibleColumnsCount: this.visibleColumnsCount });
    },
});
