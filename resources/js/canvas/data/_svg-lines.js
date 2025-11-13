export default (relationships) => ({
    connections: [{ id: 't-1', path: '' }],
    scale: 1,
    relationships: relationships,
    computeTimeout: null,
    gridSize: null, // Mesmo tamanho do grid do InfiniteCanvas
    linesLayer: null,
    initialTransform: null,
    highlightedTableId: null,
    highlightedColumnIds: [],

    init() {
        this.gridSize = Alpine.raw(this.canvas).cellSize;
        this.linesLayer = this.$el.querySelector('#lines-layer'),
        this.linesLayer.setAttribute('transform', Alpine.raw(this.canvas).getTransformString());

        window.addEventListener('canvas-zoom', () => {
            // Aplica o mesmo transform do canvas ao <g>
            // Não recomputa aqui para evitar "pulos"; as paths acompanham via transform
            const transform = Alpine.raw(this.canvas).getTransformString();
            this.linesLayer.setAttribute('transform', transform);
        });

        window.addEventListener('canvas::transform', (e) => this.linesLayer.setAttribute('transform', e.detail.transform));
        window.addEventListener('table::move', () => this.debouncedCompute());
        window.addEventListener('table::expanded', () => this.compute());
        window.addEventListener('table-side-editor::open', e => this.highlightTableConnections(e.detail?.table || e.detail?.tableId));
        window.addEventListener('table-side-editor::closed', () => this.clearHighlight());
        window.addEventListener('relationships::fresh', (event) => this.relationships = event.detail.relationships);
        window.addEventListener('tables-list::inicializeted', () => this.debouncedCompute());

        Livewire.hook('morphed',  ({ component }) => {
            if (component?.name === 'diagram.table') {
                this.debouncedCompute();
            }
        });
    },
    snapToGrid(coord) {
        return Math.round(coord / this.gridSize) * this.gridSize;
    },
    debouncedCompute() {
        // Debounce para evitar muitas atualizações
        if (this.computeTimeout) {
            clearTimeout(this.computeTimeout);
        }

        this.computeTimeout = setTimeout(() => {
            this.compute();
        }, 10);
    },
    compute() {
        this.connections = [];

        if (!this.$el) return;

        const group = this.$el.querySelector('#lines-layer');

        this.relationships.forEach(item => {
            const fromEl = document.getElementById(`column-${item.from_table_id}-${item.from_column_id}`);
            const toEl = document.getElementById(`column-${item.to_table_id}-${item.to_column_id}`);
            const fromTableEl = document.getElementById(`table-${item.from_table_id}`);
            const toTableEl = document.getElementById(`table-${item.to_table_id}`);

            if (!fromEl || !toEl) {
                return;
            }

            const fromIsCollapsed =  fromEl.closest('table').hasAttribute('is-collapsed');
            const toIsCollapsed = toEl.closest('table').hasAttribute('is-collapsed');

            if (fromIsCollapsed || toIsCollapsed) {
                return
            }

            const { aSide, bSide } = this.updateConnectionAnchorVisibility(fromEl, toEl);

            let p1 = this.getPointForSide(fromEl, aSide, group);
            let p2 = this.getPointForSide(toEl, bSide, group);

            // Calcula o path curvo que contorna os boxes
            const connection = {
                id: `${item.id}`,
                type: item.type,
                path: this.createCurvedPath(p1, p2, fromTableEl, toTableEl),
                p1, p2, aSide, bSide,
                fromId: `column-${item.from_table_id}-${item.from_column_id}`,
                toId: `column-${item.to_table_id}-${item.to_column_id}`
            };
            this.connections.push(connection);
        });

        console.log('✅ Total connections:', this.connections.length);

        this.renderSvg();
    },
    getPointForSide(boxEl, side, group) {
        const gridSize = this.gridSize;

        // Pega o ponto base em coordenadas de tela
        const rect = boxEl.getBoundingClientRect();
        const pt = this.$el.createSVGPoint();

        // Define o ponto no lado correto da box
        pt.x = side === 'right' ? rect.right : rect.left;
        pt.y = rect.top + rect.height / 2;

        // Converte para o sistema de coordenadas do <g> transformado (espaço de tela -> g)
        const gCTM = group.getScreenCTM();
        const svgP = pt.matrixTransform(gCTM.inverse());

        // Alinha ao grid
        const p = {
            x: Math.round(svgP.x / gridSize) * gridSize,
            y: Math.round(svgP.y / gridSize) * gridSize
        };

        return p;
    },
    updateConnectionAnchorVisibility(fromEl, toEl) {
        const fromRect = fromEl.getBoundingClientRect();
        const toRect = toEl.getBoundingClientRect();

        const aCenterX = fromRect.left + fromRect.width / 2;
        const bCenterX = toRect.left + toRect.width / 2;

        // Seleciona dinamicamente o lado de conexão de cada box
        // - Se A está à esquerda de B: A sai pela direita; B entra pela esquerda
        // - Se A está à direita de B: A sai pela esquerda; B entra pela direita
        const aSide = aCenterX <= bCenterX ? 'right' : 'left';
        const bSide = aCenterX <= bCenterX ? 'left' : 'right';

        // Alterna visibilidade dos pontinhos de referência
        const aAnchorLeft = fromEl.querySelector('.anchor-left');
        const aAnchorRight = fromEl.querySelector('.anchor-right');
        const bAnchorLeft = toEl.querySelector('.anchor-left');
        const bAnchorRight = toEl.querySelector('.anchor-right');

        if (aAnchorLeft && aAnchorRight) {
            aAnchorLeft.style.opacity = aSide === 'left' ? '1' : '0';
            aAnchorRight.style.opacity = aSide === 'right' ? '1' : '0';
        }
        if (bAnchorLeft && bAnchorRight) {
            bAnchorLeft.style.opacity = bSide === 'left' ? '1' : '0';
            bAnchorRight.style.opacity = bSide === 'right' ? '1' : '0';
        }

        return { aSide, bSide };
    },
    getTableBoundsInSVG(tableEl, group) {
        if (!tableEl || !group) return null;

        // Obtém o bounding box da tabela em coordenadas de tela
        const rect = tableEl.getBoundingClientRect();

        // Converte os cantos do retângulo para coordenadas SVG
        const pt = this.$el.createSVGPoint();
        const gCTM = group.getScreenCTM();

        // Canto superior esquerdo
        pt.x = rect.left;
        pt.y = rect.top;
        const topLeft = pt.matrixTransform(gCTM.inverse());

        // Canto inferior direito
        pt.x = rect.right;
        pt.y = rect.bottom;
        const bottomRight = pt.matrixTransform(gCTM.inverse());

        return {
            xMin: topLeft.x,
            xMax: bottomRight.x,
            yMin: topLeft.y,
            yMax: bottomRight.y
        };
    },
    isPointInsideTable(point, tableBounds) {
        if (!point || !tableBounds) return false;

        return point.x >= tableBounds.xMin &&
               point.x <= tableBounds.xMax &&
               point.y >= tableBounds.yMin &&
               point.y <= tableBounds.yMax;
    },
    isPointOnTableYAxis(point, tableBounds) {
        if (!point || !tableBounds) return false;

        // Verifica se o ponto está no eixo Y da tabela (Y entre yMin e yMax)
        return point.y >= tableBounds.yMin && point.y <= tableBounds.yMax;
    },
    shouldAdjustPointForLateralExit(point, tableBounds) {
        if (!point || !tableBounds) return false;

        // REGRA SIMPLES: Se o ponto está no eixo Y da tabela (Y entre yMin e yMax), SEMPRE ajustar
        // Não importa se o X está dentro ou fora - se está no eixo Y, força saída pela lateral
        const isOnYAxis = point.y >= tableBounds.yMin && point.y <= tableBounds.yMax;

        return isOnYAxis;
    },
    adjustPointToExitTable(point, tableBounds, exitSide, gridSize) {
        if (!point || !tableBounds) return point;

        // Mantém o Y original
        let newX = point.x;
        const offset = gridSize; // Offset para garantir que a linha saia completamente da tabela

        if (exitSide === 'right') {
            // Ajusta para sair pela direita (X maior)
            newX = tableBounds.xMax + offset;
        } else {
            // Ajusta para sair pela esquerda (X menor)
            newX = tableBounds.xMin - offset;
        }

        // Alinha ao grid
        newX = Math.round(newX / gridSize) * gridSize;

        return {
            x: newX,
            y: point.y
        };
    },
    adjustPointForVerticalConnection(point, tableBounds, position, gridSize) {
        if (!point || !tableBounds) return point;

        const centerX = (tableBounds.xMin + tableBounds.xMax) / 2;
        let newY = point.y;
        const offset = gridSize;

        if (position === 'bottom') {
            newY = tableBounds.yMax + offset;
        } else if (position === 'top') {
            newY = tableBounds.yMin - offset;
        }

        return {
            x: Math.round(centerX / gridSize) * gridSize,
            y: Math.round(newY / gridSize) * gridSize
        };
    },
    // Função para verificar se a linha está abaixo da tabela
    isLineBelowTable(p1, p2, tableEl, ) {
        if (!tableEl) return false;

        const group = this.$el.querySelector('#lines-layer');
        if (!group) return false;

        // Obtém o bounding box da tabela em coordenadas de tela
        const rect = tableEl.getBoundingClientRect();

        // Converte os cantos do retângulo para coordenadas SVG
        const pt = this.$el.createSVGPoint();
        const gCTM = group.getScreenCTM();

        // Canto superior esquerdo
        pt.x = rect.left;
        pt.y = rect.top;
        const topLeft = pt.matrixTransform(gCTM.inverse());

        // Canto inferior direito
        pt.x = rect.right;
        pt.y = rect.bottom;
        const bottomRight = pt.matrixTransform(gCTM.inverse());

        // Coordenadas da tabela no espaço SVG
        const tableXMin = topLeft.x;
        const tableXMax = bottomRight.x;
        const tableYMin = topLeft.y;
        const tableYMax = bottomRight.y;

        // Verifica se a linha está abaixo no eixo Y
        // (Y aumenta para baixo, então "abaixo" = Y maior)
        const isBelowY = p1.y > tableYMax && p2.y > tableYMax;

        // Verifica se a linha está abaixo/à direita no eixo X
        // (X aumenta para a direita, então "abaixo" pode significar "à direita")
        const isBelowX = p1.x > tableXMax && p2.x > tableXMax;

        // Retorna um objeto com os resultados
        return {
            belowY: isBelowY,        // Linha está abaixo no eixo Y
            belowX: isBelowX,        // Linha está à direita no eixo X
            completelyBelow: isBelowY && isBelowX,  // Está abaixo em ambos os eixos
            tableBounds: {
                xMin: tableXMin,
                xMax: tableXMax,
                yMin: tableYMin,
                yMax: tableYMax
            }
        };
    },
    createCurvedPath(p1, p2, fromTableEl, toTableEl) {
        const group = this.$el.querySelector('#lines-layer');

        // Obtém os bounds das tabelas uma vez para reutilizar
        let fromTableBounds = null;
        let toTableBounds = null;

        if (fromTableEl && group) {
            fromTableBounds = this.getTableBoundsInSVG(fromTableEl, group);
        }

        if (toTableEl && group) {
            toTableBounds = this.getTableBoundsInSVG(toTableEl, group);
        }

        let useVerticalConnection = false;
        let fromCenterX = null;
        let toCenterX = null;

        if (fromTableBounds && toTableBounds) {
            fromCenterX = (fromTableBounds.xMin + fromTableBounds.xMax) / 2;
            toCenterX = (toTableBounds.xMin + toTableBounds.xMax) / 2;

            const deltaCenterX = Math.abs(fromCenterX - toCenterX);
            if (deltaCenterX <= 300) {
                useVerticalConnection = true;
            }
        }

        if (useVerticalConnection) {
            const cellSize = this.gridSize;
            let horizontalDelta = p2.x - p1.x;
            if (horizontalDelta === 0 && fromCenterX !== null && toCenterX !== null) {
                horizontalDelta = toCenterX - fromCenterX;
            }

            const dirY = p2.y >= p1.y ? 1 : -1;
            let midY = Math.round(((p1.y + p2.y) / 2) / cellSize) * cellSize;
            if (midY === p1.y) {
                midY -= directionY * cellSize;
            }
            if (midY === p2.y) {
                midY -= dirY * cellSize;
            }

            let sideFrom = horizontalDelta >= 0 ? 1 : -1;
            let sideTo = -sideFrom;

            if (fromTableBounds && toTableBounds) {
                sideFrom = fromCenterX <= toCenterX ? 1 : -1;
                sideTo = -sideFrom;
            }

            const exitX = Math.round((p1.x + sideFrom * cellSize) / cellSize) * cellSize;
            const entryX = Math.round((p2.x + sideTo * cellSize) / cellSize) * cellSize;

            const directionY = p2.y >= p1.y ? 1 : -1;

            const pB = { x: exitX, y: p1.y };
            const pC = { x: exitX, y: midY };
            const pD = { x: entryX, y: midY };
            const pE = { x: entryX, y: p2.y };

            let path = `M ${p1.x} ${p1.y}`;
            path += ` L ${pB.x} ${pB.y}`;
            path += ` L ${pC.x} ${pC.y}`;
            path += ` L ${pD.x} ${pD.y}`;
            path += ` L ${pE.x} ${pE.y}`;
            path += ` L ${p2.x} ${p2.y}`;

            return path;
        }

        // Calcula distâncias (já calculadas acima, mas recalcula após ajustes)
        const deltaX = Math.abs(p2.x - p1.x);
        const deltaY = Math.abs(p2.y - p1.y);

        // Determina a direção da conexão
        const isfromElLeft = p1.x < p2.x;
        const isfromElAbove = p1.y < p2.y; // A acima de B

        // Raio das curvas nos cantos
        const curveRadius = 12;

        // =================================================================
        // PASSO 1: CASOS DE LINHA RETA (Prioridade Máxima)
        // =================================================================

        // Caso 1A: Boxes alinhados verticalmente (X muito próximos)
        if (deltaX < 15) {
            return `M ${p1.x} ${p1.y} L ${p2.x} ${p2.y}`;
        }

        // Caso 1B: Boxes alinhados horizontalmente (Y muito próximos)
        if (deltaY < 15) {
            return `M ${p1.x} ${p1.y} L ${p2.x} ${p2.y}`;
        }

        // Caso 1C: Boxes muito próximos (qualquer direção)
        if (deltaX < 50 && deltaY < 50) {
            return `M ${p1.x} ${p1.y} L ${p2.x} ${p2.y}`;
        }

        // =================================================================
        // PASSO 2: CASO NORMAL "L" SUAVIZADO (Com Curvas Corretas)
        // =================================================================

        let midX;
        // Lógica para determinar onde o segmento vertical irá
        // Define um X que se afasta do Box A (cria o "Z" ou "S" suave)
        if (isfromElLeft) {
            // Se A está à esquerda de B, o midX é maior que p1.x
            midX = p1.x + Math.max(this.gridSize, deltaX * 0.3);
        } else {
            // Se A está à direita de B, o midX é menor que p1.x
            midX = p1.x - Math.max(this.gridSize, deltaX * 0.3);
        }

        midX = Math.round(midX / this.gridSize) * this.gridSize;

        // Garantir que midX não cruze o X de B se deltaX for pequeno
        // Este ajuste ajuda a evitar curvas desnecessárias em proximidade
        if (isfromElLeft && midX > p2.x - curveRadius) {
            midX = p2.x - curveRadius;
        } else if (!isfromElLeft && midX < p2.x + curveRadius) {
            midX = p2.x + curveRadius;
        }

        // Se o midX for muito próximo do p1.x, force um valor mínimo
        if (Math.abs(p1.x - midX) < curveRadius * 2) {
            midX = isfromElLeft ? p1.x + curveRadius * 2 : p1.x - curveRadius * 2;
        }

        let path = `M ${p1.x} ${p1.y}`;

        // A partir de p1, o caminho vai para a horizontal, até midX

        // 1. Segmento Horizontal de p1.x até o início da curva
        const xStartCurve1 = isfromElLeft ? midX - curveRadius : midX + curveRadius;
        path += ` L ${xStartCurve1} ${p1.y}`;

        // 2. Primeira Curva: Horizontal para Vertical (no X=midX)
        // Ponto de Controle (C): midX, p1.y
        // Ponto Final (E): midX, p1.y + curva (para baixo) ou p1.y - curva (para cima)
        const yEndCurve1 = isfromElAbove ? p1.y + curveRadius : p1.y - curveRadius;
        path += ` Q ${midX} ${p1.y}, ${midX} ${yEndCurve1}`;

        // 3. Segmento Vertical: de yEndCurve1 até o início da Segunda Curva (no X=midX)
        // Ponto de Início da 2ª Curva (no X=midX)
        const yStartCurve2 = isfromElAbove ? p2.y - curveRadius : p2.y + curveRadius;
        path += ` L ${midX} ${yStartCurve2}`;

        // 4. Segunda Curva: Vertical para Horizontal (no Y=p2.y)
        // Ponto de Controle (C): midX, p2.y
        // Ponto Final (E): p2.x, p2.y
        const xEndCurve2 = isfromElLeft ? midX + curveRadius : midX - curveRadius;
        path += ` Q ${midX} ${p2.y}, ${xEndCurve2} ${p2.y}`;

        // 5. Segmento Final: Horizontal até p2.x
        path += ` L ${p2.x} ${p2.y}`;

        return path;
    },
    renderSvg() {
        const svg = this.$el;
        const group = svg.querySelector('#lines-layer'); // o <g> interno

        if (!group) return;

        // Limpa paths anteriores
        group.innerHTML = '';

        // Desenha os novos
        this.connections.forEach(conn => {
            // Cria um grupo por conexão
            const connGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');
            connGroup.dataset.fromId = conn.fromId;
            connGroup.dataset.toId = conn.toId;
            group.appendChild(connGroup);

            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', conn.path);
            path.setAttribute('stroke', '#888');
            path.setAttribute('fill', 'none');
            path.setAttribute('stroke-width', '2');
            path.setAttribute('pointer-events', 'stroke');

            connGroup.appendChild(path);

            // Desenha a barrinha vertical SEMPRE nas duas pontas
            this.drawBar(connGroup, conn.p1, conn.aSide);
            this.drawBar(connGroup, conn.p2, conn.bSide);

            // Crow's foot (pé de galinha) só no lado 'n' para 1:n, e em ambos no n:n
            const isOneToMany = conn.type === '1:n';
            const isManyToMany = conn.type === 'n:n';

            if (isOneToMany) {
                this.drawCrowsFoot(connGroup, conn.p2, conn.bSide);
                this.drawBar(connGroup, conn.p1, conn.aSide, 12);
            }

            if (isManyToMany) {
                this.drawCrowsFoot(connGroup, conn.p1, conn.aSide);
                this.drawCrowsFoot(connGroup, conn.p2, conn.bSide);
            }

            if (!isManyToMany && !isOneToMany) {
                this.drawBar(connGroup, conn.p2, conn.bSide, 12);
                this.drawBar(connGroup, conn.p1, conn.aSide, 12);
            }

            // Hover highlight
            const highlightOn = () => {
                connGroup.querySelectorAll('path').forEach(el => {
                    el.setAttribute('stroke', '#eab308');
                    el.setAttribute('stroke-width', '2.25');
                });

                // Ensure highlighted connection renders on top (SVG uses DOM order)
                const parentGroup = connGroup.parentNode;
                if (parentGroup && parentGroup.appendChild) {
                    parentGroup.appendChild(connGroup);
                }

                const fe = document.getElementById(conn.fromId);
                const te = document.getElementById(conn.toId);
                [fe, te].forEach(node => {
                    if (!node) return;
                    node.style.backgroundColor = 'rgba(250, 204, 21, 0.25)';
                    node.style.outline = '1px solid #facc15';
                    node.style.borderRadius = '4px';
                });
            };

            const highlightOff = () => {
                // Verifica se esta conexão está destacada pela tabela
                const isTableHighlighted = this.isConnectionHighlightedByTable(conn);

                // Se não estiver destacada pela tabela, remove o highlight
                if (!isTableHighlighted) {
                    connGroup.querySelectorAll('path').forEach(el => {
                        el.setAttribute('stroke', '#888');
                        el.setAttribute('stroke-width', '2');
                    });

                    const fe = document.getElementById(conn.fromId);
                    const te = document.getElementById(conn.toId);

                    // Remove highlight apenas das colunas que não estão destacadas pela tabela
                    if (fe && !this.highlightedColumnIds.includes(conn.fromId)) {
                        fe.style.backgroundColor = '';
                        fe.style.outline = '';
                        fe.style.borderRadius = '';
                    }

                    if (te && !this.highlightedColumnIds.includes(conn.toId)) {
                        te.style.backgroundColor = '';
                        te.style.outline = '';
                        te.style.borderRadius = '';
                    }
                } else {
                    // Se estiver destacada pela tabela, reaplica o highlight da tabela
                    this.applyHighlightToConnection(conn, connGroup);
                }
            };

            connGroup.addEventListener('mouseenter', highlightOn);
            connGroup.addEventListener('mouseleave', highlightOff);
        });

        // Aplica highlight se houver uma tabela destacada
        if (this.highlightedTableId) {
            this.applyHighlightToTable(this.highlightedTableId);
        }

    },
    drawBar(group, endPoint, side, offset = 8) {
        // Pequena barrinha vertical para indicar "1"
        // side: 'left' | 'right'
        const barLen = 7;
        const dir = side === 'left' ? -1 : 1;
        const ox = endPoint.x + dir * offset;
        const oy = endPoint.y;
        const half = barLen / 2;
        const bar = document.createElementNS('http://www.w3.org/2000/svg', 'path');

        bar.setAttribute('d', `M ${ox} ${oy - half} L ${ox} ${oy + half}`);
        bar.setAttribute('stroke', '#888');
        bar.setAttribute('fill', 'none');
        bar.setAttribute('stroke-width', '2');
        bar.setAttribute('stroke-linecap', 'round');
        bar.setAttribute('pointer-events', 'stroke');

        group.appendChild(bar);
    },
    drawCrowsFoot(group, endPoint, side) {
        // Crow's foot clássico: três linhas que partem do mesmo ponto e abrem em leque
        // side: 'left' | 'right'

        const dir = side === 'left' ? -1 : 1; // direção para fora do box
        const hookOffset = 8; // distância da ponta do campo até onde começa o pé
        const length = 14;    // comprimento de cada "dedo"
        const spreadDeg = 35; // abertura das barras externas

        // Origem levemente afastada do endpoint para não sobrepor a borda do box
        const ox = endPoint.x + dir * hookOffset;
        const oy = endPoint.y;

        // Ângulo base: 0 (direita) ou 180° (esquerda)
        const baseAngle = side === 'right' ? 0 : Math.PI;
        const angles = [
            baseAngle - (Math.PI / 180) * spreadDeg,
            baseAngle,
            baseAngle + (Math.PI / 180) * spreadDeg,
        ];

        angles.forEach(a => {
            // Inverte a direção para apontar em direção à tabela (mesmo lado do endpoint)
            const x2 = ox - Math.cos(a) * length;
            const y2 = oy - Math.sin(a) * length;

            const seg = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            seg.setAttribute('d', `M ${ox} ${oy} L ${x2} ${y2}`);
            seg.setAttribute('stroke', '#888');
            seg.setAttribute('fill', 'none');
            seg.setAttribute('stroke-width', '2');
            seg.setAttribute('pointer-events', 'stroke');

            group.appendChild(seg);
        });
    },
    highlightTableConnections(tableId) {
        // Limpa o highlight anterior
        this.clearHighlight();

        if (!tableId) {
            this.highlightedTableId = null;
            return;
        }

        this.highlightedTableId = tableId;

        // Aplica o highlight IMEDIATAMENTE
        this.applyHighlightToTable(tableId);
    },
    clearHighlight() {
        const group = this.$el?.querySelector('#lines-layer');
        if (!group) return;

        // Limpa o highlight das linhas
        if (this.highlightedTableId) {
            const previousTableIdNum = parseInt(this.highlightedTableId);
            const previousConnections = this.connections.filter(conn => {
                const fromTableId = parseInt(conn.fromId.split('-')[1]);
                const toTableId = parseInt(conn.toId.split('-')[1]);
                return fromTableId === previousTableIdNum || toTableId === previousTableIdNum;
            });

            previousConnections.forEach(conn => {
                const connGroup = group.querySelector(`g[data-from-id="${conn.fromId}"][data-to-id="${conn.toId}"]`);
                if (!connGroup) return;

                // Restaura o estilo padrão das linhas
                connGroup.querySelectorAll('path').forEach(el => {
                    el.setAttribute('stroke', '#888');
                    el.setAttribute('stroke-width', '2');
                });
            });
        }

        // Limpa o highlight das colunas
        this.highlightedColumnIds.forEach(columnId => {
            const columnEl = document.getElementById(columnId);
            if (columnEl) {
                columnEl.style.backgroundColor = '';
                columnEl.style.outline = '';
                columnEl.style.borderRadius = '';
            }
        });

        this.highlightedColumnIds = [];
        this.highlightedTableId = null;
    },
    isConnectionHighlightedByTable(conn) {
        if (!this.highlightedTableId) return false;

        const tableIdNum = parseInt(this.highlightedTableId);
        const fromTableId = parseInt(conn.fromId.split('-')[1]);
        const toTableId = parseInt(conn.toId.split('-')[1]);

        return fromTableId === tableIdNum || toTableId === tableIdNum;
    },
    applyHighlightToConnection(conn, connGroup) {
        const group = this.$el?.querySelector('#lines-layer');
        if (!group || !connGroup) return;

        // Aplica o mesmo estilo do highlight da tabela
        connGroup.querySelectorAll('path').forEach(el => {
            el.setAttribute('stroke', '#eab308');
            el.setAttribute('stroke-width', '2.25');
        });

        // Move para o topo (renderiza por último)
        if (group && group.appendChild) {
            group.appendChild(connGroup);
        }

        // Destaca as colunas relacionadas
        const fe = document.getElementById(conn.fromId);
        const te = document.getElementById(conn.toId);
        [fe, te].forEach(node => {
            if (!node) return;
            node.style.backgroundColor = 'rgba(250, 204, 21, 0.25)';
            node.style.outline = '1px solid #facc15';
            node.style.borderRadius = '4px';
        });
    },
    applyHighlightToTable(tableId) {
        const group = this.$el?.querySelector('#lines-layer');
        if (!group) return;

        // Converte tableId para número para comparação
        const tableIdNum = parseInt(tableId);

        // Encontra todas as conexões relacionadas a esta tabela
        const relatedConnections = this.connections.filter(conn => {
            // Extrai o table_id do fromId e toId (formato: "column-{table_id}-{column_id}")
            const fromTableId = parseInt(conn.fromId.split('-')[1]);
            const toTableId = parseInt(conn.toId.split('-')[1]);

            return fromTableId === tableIdNum || toTableId === tableIdNum;
        });

        // Aplica highlight em todas as conexões relacionadas
        relatedConnections.forEach(conn => {
            const connGroup = group.querySelector(`g[data-from-id="${conn.fromId}"][data-to-id="${conn.toId}"]`);
            if (!connGroup) return;

            // Aplica o mesmo estilo do highlightOn
            connGroup.querySelectorAll('path').forEach(el => {
                el.setAttribute('stroke', '#eab308');
                el.setAttribute('stroke-width', '2.25');
            });

            // Move para o topo (renderiza por último)
            if (group && group.appendChild) {
                group.appendChild(connGroup);
            }

            // Destaca as colunas relacionadas
            const fe = document.getElementById(conn.fromId);
            const te = document.getElementById(conn.toId);
            [fe, te].forEach(node => {
                if (!node) return;
                node.style.backgroundColor = 'rgba(250, 204, 21, 0.25)';
                node.style.outline = '1px solid #facc15';
                node.style.borderRadius = '4px';

                // Armazena o ID da coluna para poder limpar depois
                if (!this.highlightedColumnIds.includes(conn.fromId)) {
                    this.highlightedColumnIds.push(conn.fromId);
                }
                if (!this.highlightedColumnIds.includes(conn.toId)) {
                    this.highlightedColumnIds.push(conn.toId);
                }
            });
        });
    }
});
