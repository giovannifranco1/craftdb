import { InfiniteCanvas } from '../classes/InfiniteCanvas.js'
import cursorBase from '@images/icons/cursor.svg';
import confetti from 'canvas-confetti'; 'canvas-confetti';

// @Alpine.js data
export default (cellSize = 25, isInitialLoad = false, [offsetX, offsetY] = []) => ({
    canvas: new InfiniteCanvas(cellSize, [offsetX, offsetY]),
    canvasElement: document.getElementById('canvas'),
    zoomPercent: 75,
    contextMenuPosition: { x: 100, y: 100 },
    isInRelationshipEvent: false,
    isInAddTableEvent: false,
    isInAddAreaEvent: false,
    isInAddNoteEvent: false,
    addTableClickHandler: null,
    addAreaClickHandler: null,
    addNoteClickHandler: null,
    relationshipEvent: {
        from_table_id: null,
        to_table_id: null,
        type: null
    },

    init() {
        this.setupListeners();

        if (isInitialLoad) {
            confetti({
                particleCount: 200,
                spread: 70,
                origin: { y: 0.6 }
            });
        }
    },

    setupListeners() {
        // Listener para teclas de atalho
        window.addEventListener('keydown', (e) => this.keydownListeners(e));

        // Listener para salvar posições das tabelas
        this.$el.addEventListener('save-positions', () => this.saveTablesPositions());

        // Listener para ativar modo de adicionar tabela
        this.$el.addEventListener('table::adding', () => {
            this.isInAddTableEvent = true;
        });

        // Listener para ativar modo de adicionar área
        this.$el.addEventListener('area::adding', () => {
            this.isInAddAreaEvent = true;
        });

        // Listener para ativar modo de adicionar nota
        this.$el.addEventListener('note::adding', () => {
            this.isInAddNoteEvent = true;
        });

        // Listener para renomear table
        window.addEventListener('table::added', (e) => {
            const tableId = e.detail?.tableId || e.detail?.[0]?.tableId || (typeof e.detail === 'number' ? e.detail : null);

            if (tableId) {
                requestAnimationFrame(() => {
                    const findTableElement = (attempts = 0) => {
                        const tableElement = document.querySelector(`[table-box][table-id="${tableId}"]`);

                        if (tableElement) {
                            this.$dispatch('table::renaming', { el: tableElement });
                        } else if (attempts < 20) {
                            setTimeout(() => findTableElement(attempts + 1), 50);
                        }
                    };

                    findTableElement();
                });
            }
        });

        // Listener para spotlight selecionar tabela
        window.addEventListener('spotlight::table-selected', (e) => {
            const { tableId, x, y } = e.detail[0];

            if (x && y) {
                // Calcula offsets para centralizar a tabela na viewport
                const canvas = Alpine.raw(this.canvas);
                const scale = canvas.getScale();

                if (this.canvasElement) {
                    const canvasWidth = this.canvasElement.clientWidth;
                    const canvasHeight = this.canvasElement.clientHeight;

                    const tableWidth = 300;
                    const tableHeight = 200; // altura aproximada

                    const offsetX = x - (canvasWidth / 2 - tableWidth / 2) / scale;
                    const offsetY = y - (canvasHeight / 2 - tableHeight / 2) / scale;

                    canvas.setPositionOffsets(offsetX, offsetY);
                }
            }

            Livewire.dispatch('table-side-editor::open', { tableId: tableId });

            setTimeout(() => this.$flux.modal('spotlight')?.close(), 200);
        });

        if (this.canvasElement) {
            this.canvasElement.addEventListener('contextmenu', (e) => {
                const virtualPos = Alpine.raw(this.canvas).clientToVirtual(e.clientX, e.clientY);
                this.contextMenuPosition = virtualPos;
            });
        }

        this.$watch('isInRelationshipEvent', (value) => {
            const body = document.querySelector('body');

            if (body) {
                if (value) {
                    body.style.cursor = 'crosshair';
                } else {
                    body.style.cursor = `url(${cursorBase}) 3.5 3.5, auto`;
                }
            }
        });

        this.$watch('isInAddTableEvent', (value) => {
            const body = document.querySelector('body');

            if (body) {
                if (value) {
                    body.style.cursor = 'crosshair';
                } else {
                    body.style.cursor = `url(${cursorBase}) 3.5 3.5, auto`;
                }
            }

            if (this.canvasElement) {
                if (this.addTableClickHandler) {
                    this.canvasElement.removeEventListener('click', this.addTableClickHandler);
                    this.addTableClickHandler = null;
                }

                if (value) {
                    this.addTableClickHandler = (e) => {
                        e.preventDefault();
                        e.stopPropagation();

                        const virtualPos = Alpine.raw(this.canvas).clientToVirtual(e.clientX, e.clientY);

                        if (window.Livewire) {
                            window.Livewire.dispatch('table::add', {
                                x: Math.round(virtualPos.x),
                                y: Math.round(virtualPos.y)
                            });
                        }

                        this.isInAddTableEvent = false;

                        if (this.addTableClickHandler) {
                            this.canvasElement.removeEventListener('click', this.addTableClickHandler);
                            this.addTableClickHandler = null;
                        }
                    };

                    this.canvasElement.addEventListener('click', this.addTableClickHandler, { once: true });
                }
            }
        });

        this.$watch('isInAddAreaEvent', (value) => {
            const body = document.querySelector('body');

            if (body) {
                if (value) {
                    body.style.cursor = 'crosshair';
                } else {
                    body.style.cursor = `url(${cursorBase}) 3.5 3.5, auto`;
                }
            }

            if (this.canvasElement) {
                if (this.addAreaClickHandler) {
                    this.canvasElement.removeEventListener('click', this.addAreaClickHandler);
                    this.addAreaClickHandler = null;
                }

                if (value) {
                    this.addAreaClickHandler = (e) => {
                        e.preventDefault();
                        e.stopPropagation();

                        const virtualPos = Alpine.raw(this.canvas).clientToVirtual(e.clientX, e.clientY);

                        if (window.Livewire) {
                            window.Livewire.dispatch('area::add', {
                                x: Math.round(virtualPos.x),
                                y: Math.round(virtualPos.y)
                            });
                        }

                        this.isInAddAreaEvent = false;

                        if (this.addAreaClickHandler) {
                            this.canvasElement.removeEventListener('click', this.addAreaClickHandler);
                            this.addAreaClickHandler = null;
                        }
                    };

                    this.canvasElement.addEventListener('click', this.addAreaClickHandler, { once: true });
                }
            }
        });

        this.$watch('isInAddNoteEvent', (value) => {
            const body = document.querySelector('body');

            if (body) {
                if (value) {
                    body.style.cursor = 'crosshair';
                } else {
                    body.style.cursor = `url(${cursorBase}) 3.5 3.5, auto`;
                }
            }

            if (this.canvasElement) {
                if (this.addNoteClickHandler) {
                    this.canvasElement.removeEventListener('click', this.addNoteClickHandler);
                    this.addNoteClickHandler = null;
                }

                if (value) {
                    this.addNoteClickHandler = (e) => {
                        e.preventDefault();
                        e.stopPropagation();

                        const virtualPos = Alpine.raw(this.canvas).clientToVirtual(e.clientX, e.clientY);
                        const noteData = {
                            x: Math.round(virtualPos.x),
                            y: Math.round(virtualPos.y)
                        };

                        if (window.Livewire) {
                            window.Livewire.dispatch('note::add', noteData);
                        }

                        this.isInAddNoteEvent = false;

                        if (this.addNoteClickHandler) {
                            this.canvasElement.removeEventListener('click', this.addNoteClickHandler);
                            this.addNoteClickHandler = null;
                        }
                    };

                    this.canvasElement.addEventListener('click', this.addNoteClickHandler, { once: true });
                }
            }
        });
    },

    keydownListeners(e) {
        this.handleMetaS(e);
    },

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
