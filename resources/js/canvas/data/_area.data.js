// Contador global para z-index (inicia em 500 para áreas ficarem abaixo de tabelas)
if (typeof window.areaZIndexCounter === 'undefined') {
    window.areaZIndexCounter = 500;
}

// @Alpine.js data
export default (x, y, width, height, areaId) => ({
    x: x,
    y: y,
    areaId: areaId,
    width: width,
    height: height,
    zIndex: null,
    isDragging: false,
    isResizing: false,
    resizeDirection: null,
    startX: 0,
    startY: 0,
    initialX: 0,
    initialY: 0,
    initialWidth: 0,
    initialHeight: 0,
    canDragBySpace: true,

    init() {
        // Snap inicial ao grid
        this.x = this.snapToGrid(this.x);
        this.y = this.snapToGrid(this.y);
        this.width = this.snapToGrid(this.width);
        this.height = this.snapToGrid(this.height);

        // Set initial styles
        this.$el.style.transform = `translate3d(${this.x}px, ${this.y}px, 0)`;
        this.$el.style.left = '0px';
        this.$el.style.top = '0px';
        this.$el.style.width = `${this.width}px`;
        this.$el.style.height = `${this.height}px`;
        this.$el.style.maxWidth = `${this.width}px`;
        this.$el.style.maxHeight = `${this.height}px`;

        window.addEventListener('canvas::space', (e) => {
            this.canDragBySpace = !e.detail.isSpacePressed;
            this.updateCursorBySpace();
        });

        this.updateCursorBySpace = () => {
            if (!this.canDragBySpace && !this.isResizing) {
                this.$el.style.cursor = 'grab';
            } else if (!this.isResizing) {
                this.$el.style.cursor = '';
            }
        };

        this.updateCursorBySpace();

        // Drag listeners
        this.$el.addEventListener('mousedown', (e) => {
            const isResizeHandle = e.target.classList.contains('resize-handle') ||
                                   e.target.closest('.resize-handle') !== null;

            // Verificar se estamos em modo de adicionar tabela ou nota
            const isInAddMode = this.isInAddTableOrNoteMode();

            // Se estiver em modo de adicionar, permitir que o evento propague
            if (isInAddMode) {
                return;
            }

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
        this.$refs.canvasRoot.addEventListener('mouseup', () => {
            this.endDrag();
            this.endResize();
        });
        this.$refs.canvasRoot.addEventListener('mouseleave', () => {
            this.endDrag();
            this.endResize();
        });
    },

    // setupListeners() {
    //     window.addEventListener(`area::color-updated.${this.areaId}`, () => {
    //         this.color = this.color;
    //     });
    // },

    getCanvas() {
        // Access canvas from canvasRoot Alpine data
        if (this.$refs.canvasRoot) {
            const canvasData = Alpine.$data(this.$refs.canvasRoot);
            if (canvasData && canvasData.canvas) {
                return Alpine.raw(canvasData.canvas);
            }
        }
        return null;
    },

    getCanvasData() {
        // Access canvas data from canvasRoot Alpine data
        if (this.$refs.canvasRoot) {
            return Alpine.$data(this.$refs.canvasRoot);
        }
        return null;
    },

    isInAddTableOrNoteMode() {
        const canvasData = this.getCanvasData();
        if (canvasData) {
            return canvasData.isInAddTableEvent || canvasData.isInAddNoteEvent;
        }
        return false;
    },

    getScale() {
        const canvas = this.getCanvas();
        return canvas ? canvas.getScale() : 1;
    },

    getGridSize() {
        const canvas = this.getCanvas();
        return canvas ? (canvas.cellSize || 25) : 25;
    },

    getSnappedWidth() {
        const grid = this.getGridSize();
        return Math.max(grid * 4, Math.round(this.width / grid) * grid);
    },

    getSnappedHeight() {
        const grid = this.getGridSize();
        return Math.max(grid * 4, Math.round(this.height / grid) * grid);
    },

    snapToGrid(coord) {
        const gridSize = this.getGridSize();
        return Math.round(coord / gridSize) * gridSize;
    },

    // Sync Alpine state from actual DOM (position + size) so drag works after resize/re-render
    syncStateFromDOM() {
        const inlineTransform = this.$el.style.transform;
        const translateMatch = inlineTransform?.match(/translate3d\((-?\d+(?:\.\d+)?)px,\s*(-?\d+(?:\.\d+)?)px/);
        if (translateMatch) {
            this.x = parseFloat(translateMatch[1]);
            this.y = parseFloat(translateMatch[2]);
        } else {
            const matrix = getComputedStyle(this.$el).transform;
            if (matrix && matrix !== 'none') {
                const m = matrix.match(/matrix\((.+),\s*(.+),\s*(.+),\s*(.+),\s*(.+),\s*(.+)\)/);
                if (m) {
                    this.x = parseFloat(m[5]);
                    this.y = parseFloat(m[6]);
                }
            }
        }
        // Dimensions: use actual rendered size so state matches after resize
        const w = this.$el.offsetWidth;
        const h = this.$el.offsetHeight;
        if (w > 0 && h > 0) {
            this.width = w;
            this.height = h;
        }
    },

    setHighestZIndex() {
        window.areaZIndexCounter += 1;
        const newZIndex = window.areaZIndexCounter;
        this.zIndex = newZIndex;
        this.$el.style.zIndex = newZIndex;
        return newZIndex;
    },

    // ---- DRAG ----
    startDrag(e) {
        if (!this.canDragBySpace || this.isResizing) return;

        this.isDragging = true;

        this.startX = e.clientX;
        this.startY = e.clientY;

        // Sync state from DOM so x, y, width, height match actual rendered values (after resize/re-render)
        this.syncStateFromDOM();
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

        let nextX = this.initialX + dx;
        let nextY = this.initialY + dy;

        const snappedX = this.snapToGrid(nextX);
        const snappedY = this.snapToGrid(nextY);

        this.x = snappedX;
        this.y = snappedY;

        this.$el.style.left = '0px';
        this.$el.style.top = '0px';
        this.$el.style.transform = `translate3d(${this.x}px, ${this.y}px, 0)`;
        // Preserve current width/height during drag (from last resize)
        this.$el.style.width = `${this.width}px`;
        this.$el.style.height = `${this.height}px`;
        this.$el.style.maxWidth = `${this.width}px`;
        this.$el.style.maxHeight = `${this.height}px`;
    },

    endDrag() {
        if (!this.isDragging) return;
        this.isDragging = false;
        this.$dispatch(`area::move.${this.areaId}`, { x: this.x, y: this.y });
    },

    // ---- RESIZE ----
    startResize(direction, e) {
        e.preventDefault();
        e.stopPropagation();

        // Prevent drag from starting
        this.isDragging = false;

        this.isResizing = true;
        this.resizeDirection = direction;
        this.startX = e.clientX;
        this.startY = e.clientY;
        this.initialX = this.x;
        this.initialY = this.y;
        this.initialWidth = this.width;
        this.initialHeight = this.height;
        this.$el.style.transition = 'none';

        this.setHighestZIndex();

        // Prevent text selection during resize
        document.body.style.userSelect = 'none';
    },

    resize(e) {
        if (!this.isResizing) return;
        e.preventDefault();
        e.stopPropagation();

        const scale = this.getScale();
        const dx = (e.clientX - this.startX) / scale;
        const dy = (e.clientY - this.startY) / scale;

        let newX = this.initialX;
        let newY = this.initialY;
        let newWidth = this.initialWidth;
        let newHeight = this.initialHeight;

        const grid = this.getGridSize();
        const minSize = grid * 4;

        switch (this.resizeDirection) {
            case 'right':
                newWidth = Math.max(minSize, this.initialWidth + dx);
                break;
            case 'left':
                newWidth = Math.max(minSize, this.initialWidth - dx);
                newX = this.initialX + (this.initialWidth - newWidth);
                break;
            case 'bottom':
                newHeight = Math.max(minSize, this.initialHeight + dy);
                break;
            case 'top':
                newHeight = Math.max(minSize, this.initialHeight - dy);
                newY = this.initialY + (this.initialHeight - newHeight);
                break;
            case 'top-right':
                newHeight = Math.max(minSize, this.initialHeight - dy);
                newY = this.initialY + (this.initialHeight - newHeight);
                newWidth = Math.max(minSize, this.initialWidth + dx);
                break;
            case 'top-left':
                newHeight = Math.max(minSize, this.initialHeight - dy);
                newY = this.initialY + (this.initialHeight - newHeight);
                newWidth = Math.max(minSize, this.initialWidth - dx);
                newX = this.initialX + (this.initialWidth - newWidth);
                break;
            case 'bottom-right':
                newWidth = Math.max(minSize, this.initialWidth + dx);
                newHeight = Math.max(minSize, this.initialHeight + dy);
                break;
            case 'bottom-left':
                newWidth = Math.max(minSize, this.initialWidth - dx);
                newX = this.initialX + (this.initialWidth - newWidth);
                newHeight = Math.max(minSize, this.initialHeight + dy);
                break;
        }

        // Snap to grid
        this.x = this.snapToGrid(newX);
        this.y = this.snapToGrid(newY);
        this.width = this.snapToGrid(newWidth);
        this.height = this.snapToGrid(newHeight);

        // Update element styles directly
        this.$el.style.left = '0px';
        this.$el.style.top = '0px';
        this.$el.style.transform = `translate3d(${this.x}px, ${this.y}px, 0)`;
        this.$el.style.width = `${this.width}px`;
        this.$el.style.height = `${this.height}px`;
        this.$el.style.maxWidth = `${this.width}px`;
        this.$el.style.maxHeight = `${this.height}px`;
    },

    endResize() {
        if (!this.isResizing) return;

        const savedWidth = this.width;
        const savedHeight = this.height;
        this.isResizing = false;
        this.resizeDirection = null;

        document.body.style.userSelect = '';

        this.x = this.snapToGrid(this.x);
        this.y = this.snapToGrid(this.y);
        this.width = this.snapToGrid(savedWidth);
        this.height = this.snapToGrid(savedHeight);

        this.$el.style.width = `${savedWidth}px`;
        this.$el.style.height = `${savedHeight}px`;
        this.$el.style.maxWidth = `${savedWidth}px`;
        this.$el.style.maxHeight = `${savedHeight}px`;

        this.$dispatch(`area::resize.${this.areaId}`, {x: this.x, y: this.y, width: savedWidth, height: savedHeight });
    },
});

