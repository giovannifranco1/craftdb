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
        window.addEventListener('keydown', (e) => this.keydownListeners(e));
        this.$el.addEventListener('save-positions', () => this.saveTablesPositions());

        // Listener para spotlight selecionar tabela
        window.addEventListener('spotlight::table-selected', (e) => {
            const { tableId, x, y } = e.detail[0];

            if (x && y) {
                // Calcula offsets para centralizar a tabela na viewport
                const canvas = Alpine.raw(this.canvas);
                const scale = canvas.getScale();
                const canvasElement = document.getElementById('canvas');

                if (canvasElement) {
                    const canvasWidth = canvasElement.clientWidth;
                    const canvasHeight = canvasElement.clientHeight;

                    const tableWidth = 300;
                    const tableHeight = 200; // altura aproximada

                    const offsetX = x - (canvasWidth / 2 - tableWidth / 2) / scale;
                    const offsetY = y - (canvasHeight / 2 - tableHeight / 2) / scale;

                    canvas.setPositionOffsets(offsetX, offsetY);
                }
            }

            // Abre o table-side-editor
            if (window.Livewire) {
                window.Livewire.dispatch('table-side-editor::open', { table: tableId });
            } else {
                $dispatch('table-side-editor::open', { table: tableId });
            }

            setTimeout(() => this.$flux.modal('spotlight')?.close(), 200);
        });

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
                particleCount: 200,
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

    keydownListeners(e) {
        // this.handleSpotlight(e);
        this.handleMetaS(e);
    },

    // handleSpotlight(e) {
    //     const isMetaK = e.metaKey && e.key === 'k';
    //     const isCtrlK = e.ctrlKey && e.key === 'k';

    //     if (isMetaK || isCtrlK) {
    //        this.$flux.modal('spotlight').show();
    //     }
    // },

    handleMetaS(e) {
        const isMetaS = e.metaKey && e.key === 's';
        const isCtrlS = e.ctrlKey && e.key === 's';

        if (isMetaS || isCtrlS) {
            e.preventDefault();
            this.saveTablesPositions();
        }
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

});
