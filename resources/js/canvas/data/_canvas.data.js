import { InfiniteCanvas } from '../classes/InfiniteCanvas.js'

// @Alpine.js data
export default (cellSize = 25) => ({
    canvas: new InfiniteCanvas(cellSize),
    zoomPercent: 75,
    init() {
        const handleKeyDown = (e) => this.handleMetaS(e);
        window.addEventListener('keydown', handleKeyDown);

        this.$el.addEventListener('save-positions', () => {
            this.saveTablesPositions();
        });
    },

    saveTablesPositions() {
        // Coleta todas as tabelas com suas coordenadas
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
