import { InfiniteCanvas } from '../classes/InfiniteCanvas.js'
import cursorBase from '@images/icons/cursor.svg';
import confetti from 'canvas-confetti'; 'canvas-confetti';

// @Alpine.js data
export default (cellSize = 25, isInitialLoad = false, [offsetX, offsetY] = []) => ({
    canvas: new InfiniteCanvas(cellSize, [offsetX, offsetY]),
    zoomPercent: 75,
    contextMenuPosition: { x: 100, y: 100 },
    isInRelationshipEvent: false,
    relationshipEvent: {
        from_table_id: null,
        to_table_id: null,
        type: null
    },

    init() {
        window.addEventListener('keydown', (e) => this.handleMetaS(e));
        this.$el.addEventListener('save-positions', () => this.saveTablesPositions());

        // Captura a posição do clique direito no canvas
        const canvasElement = document.getElementById('canvas');
        if (canvasElement) {
            canvasElement.addEventListener('contextmenu', (e) => {
                const virtualPos = Alpine.raw(this.canvas).clientToVirtual(e.clientX, e.clientY);
                this.contextMenuPosition = virtualPos;
            });
        }

        if (isInitialLoad) {
            confetti({
                particleCount: 100,
                spread: 70,
                origin: { y: 0.6 }
            });
        }


        this.$watch('isInRelationshipEvent', (value) => {
            const body = document.querySelector('body');

            if (body) {
                if (value) {
                    body.style.cursor = 'crosshair';
                } else {
                    body.style.cursor = `url(${cursorBase}) 6 6, pointer`;
                }
            }
        });

    },

    saveTablesPositions() {
        const tables = [];
        const tableElements = this.$el.querySelectorAll('[table-box][table-id]');

        tableElements.forEach(tableEl => {
            const tableId = tableEl.getAttribute('table-id');
            const tableData = Alpine.$data(tableEl);

            if (tableId && tableData) {
                tables.push({
                    id: parseInt(tableId),
                    x: tableData.x,
                    y: tableData.y
                });
            }
        });

        const diagramId = this.$el.getAttribute('diagram-id');

        if (tables.length > 0 && diagramId) {
            Livewire.dispatch('save-tables-positions', {
                diagramId: diagramId,
                tables: tables
            });
        }
    },

    handleMetaS(e) {
        const isMetaS = e.metaKey && e.key === 's';
        const isCtrlS = e.ctrlKey && e.key === 's';

        if (isMetaS || isCtrlS) {
            e.preventDefault();
            this.saveTablesPositions();
        }
    }

});
