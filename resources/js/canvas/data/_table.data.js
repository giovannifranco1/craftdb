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
    startX: 0,
    startY: 0,
    initialX: 0,
    initialY: 0,
    width: 300, // valor inicial (suave para grid 25px)
    minHeight: null,
    resizeStartX: 0,
    initialWidth: 0,
    colWidths: [70, 50], // Column resize state
    expanded: true,


    init() {
        // Snap inicial ao grid (caso valores venham desalinhados do backend)
        this.x = this.snapToGrid(this.x);
        this.y = this.snapToGrid(this.y);

        this.$el.style.transform = `translate3d(${this.x}px, ${this.y}px, 0)`;
        this.$el.style.left = '0px';
        this.$el.style.top = '0px';
        this.minHeight = 13 * this.getGridSize();

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

        this.$el.addEventListener('mousedown', (e) => this.startDrag(e));
        this.$refs.canvasRoot.addEventListener('mousemove', (e) => this.drag(e));
        this.$refs.canvasRoot.addEventListener('mouseup', (e) => this.endDrag(e));
        this.$refs.canvasRoot.addEventListener('mouseleave', (e) => this.endDrag(e)); // chama endDrag ao sair do canvasRoot
        this.$root.addEventListener('dropdown-menu::opened', () => this.setHighestZIndex());
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

        // Define z-index alto apenas uma vez quando o drag começa
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
        this.$dispatch('table::move');
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

        setTimeout(() => {this.$dispatch('table::expanded')}, 100);
    }
    // ---- RESIZE ------
});
