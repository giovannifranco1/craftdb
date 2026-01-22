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
    colorLines: '#73ABEF',
    // Performance optimizations
    svgElementCache: new Map(), // Cache de elementos SVG por connection ID
    domElementCache: new Map(), // Cache de elementos DOM das colunas/tabelas
    boundingRectCache: new Map(), // Cache de BoundingClientRect (CRÍTICO)
    anchorCache: new Map(), // Cache de elementos .anchor-left/.anchor-right
    transformCache: null, // Cache de gCTM.inverse() para reutilizar
    linesLayerCache: null, // Cache do #lines-layer
    rafId: null, // RequestAnimationFrame ID
    isDragging: false, // Flag para saber se está em drag

    init() {
        this.gridSize = Alpine.raw(this.canvas).cellSize;
        this.linesLayer = this.$el.querySelector('#lines-layer');
        this.linesLayerCache = this.linesLayer; // Cache permanente
        this.linesLayer.setAttribute('transform', Alpine.raw(this.canvas).getTransformString());

        window.addEventListener('canvas-zoom', () => {
            // Aplica o mesmo transform do canvas ao <g>
            // Não recomputa aqui para evitar "pulos"; as paths acompanham via transform
            const transform = Alpine.raw(this.canvas).getTransformString();
            this.linesLayer.setAttribute('transform', transform);
            this.invalidateBoundingRectCache(); // Zoom altera posições
        });

        window.addEventListener('canvas::transform', (e) => {
            this.linesLayer.setAttribute('transform', e.detail.transform);
            this.invalidateBoundingRectCache(); // Transform altera posições
        });

        window.addEventListener('table::move', (e) => {this.debouncedCompute(); this.highlightTableConnections(e.detail?.tableId)});
        window.addEventListener('table::expanded', () => this.compute());
        window.addEventListener('table-side-editor::open', e => this.highlightTableConnections(e.detail?.tableId));
        window.addEventListener('table-side-editor::closed', () => this.clearHighlight());
        window.addEventListener('relationships::fresh', (event) => this.relationships = event.detail.relationships || []);
        window.addEventListener('table-list::inicializeted', (event) => {
            const offsets = event.detail;

            if (offsets) {
                Alpine.raw(this.canvas).setPositionOffsets(offsets[0], offsets[1])
            }

            this.debouncedCompute();
        });

        Livewire.hook('morphed',  ({ component }) => {
            if (component?.name === 'diagram.table') {
                this.debouncedCompute();
            }
        });

        // Limpa caches quando tabelas são adicionadas/removidas
        window.addEventListener('table::added', () => {
            this.clearDOMCache();
            this.invalidateBoundingRectCache();
        });
        window.addEventListener('table::removed', () => {
            this.clearDOMCache();
            this.invalidateBoundingRectCache();
        });
        window.addEventListener('table::relationship-added', () => {
            this.clearDOMCache();
            this.invalidateBoundingRectCache();
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
            this.computeWithRAF();
        }, 10);
    },
    computeWithRAF() {
        // Usa requestAnimationFrame para agrupar atualizações visuais
        if (this.rafId) {
            cancelAnimationFrame(this.rafId);
        }

        this.rafId = requestAnimationFrame(() => {
            this.compute();
            this.rafId = null;
        });
    },
    compute() {
        this.connections = [];

        if (!this.$el) return;

        // Usa cache do lines-layer ao invés de querySelector
        const group = this.linesLayerCache || this.$el.querySelector('#lines-layer');

        // Invalida caches no início do compute para pegar novas posições
        this.transformCache = null;
        this.boundingRectCache.clear(); // CRÍTICO: Limpa cache de posições para pegar novas coordenadas

        this.relationships.forEach(item => {
            // Cache de elementos DOM para reduzir queries
            const fromId = `column-${item.from_table_id}-${item.from_column_id}`;
            const toId = `column-${item.to_table_id}-${item.to_column_id}`;
            const fromTableId = `table-${item.from_table_id}`;
            const toTableId = `table-${item.to_table_id}`;

            // Usa cache com validação de elementos no DOM
            let fromEl = this.domElementCache.get(fromId);
            if (!fromEl || !document.body.contains(fromEl)) {
                fromEl = document.getElementById(fromId);
                if (fromEl) {
                    this.domElementCache.set(fromId, fromEl);
                } else {
                    this.domElementCache.delete(fromId);
                }
            }

            let toEl = this.domElementCache.get(toId);
            if (!toEl || !document.body.contains(toEl)) {
                toEl = document.getElementById(toId);
                if (toEl) {
                    this.domElementCache.set(toId, toEl);
                } else {
                    this.domElementCache.delete(toId);
                }
            }

            let fromTableEl = this.domElementCache.get(fromTableId);
            if (!fromTableEl || !document.body.contains(fromTableEl)) {
                fromTableEl = document.getElementById(fromTableId);
                if (fromTableEl) {
                    this.domElementCache.set(fromTableId, fromTableEl);
                } else {
                    this.domElementCache.delete(fromTableId);
                }
            }

            let toTableEl = this.domElementCache.get(toTableId);
            if (!toTableEl || !document.body.contains(toTableEl)) {
                toTableEl = document.getElementById(toTableId);
                if (toTableEl) {
                    this.domElementCache.set(toTableId, toTableEl);
                } else {
                    this.domElementCache.delete(toTableId);
                }
            }

            if (!fromEl || !toEl) {
                return;
            }

            // Verifica se elementos ainda estão no DOM
            const fromTable = fromEl.closest('table');
            const toTable = toEl.closest('table');

            if (!fromTable || !toTable) {
                // Elementos não estão mais no DOM, limpa cache e pula
                this.domElementCache.delete(fromId);
                this.domElementCache.delete(toId);
                this.domElementCache.delete(fromTableId);
                this.domElementCache.delete(toTableId);
                return;
            }

            const fromIsCollapsed = fromTable.hasAttribute('is-collapsed');
            const toIsCollapsed = toTable.hasAttribute('is-collapsed');

            if (fromIsCollapsed || toIsCollapsed) {
                return
            }

            const { aSide, bSide } = this.updateConnectionAnchorVisibility(fromEl, toEl, fromId, toId);

            let p1 = this.getPointForSide(fromEl, aSide, group, fromId);
            let p2 = this.getPointForSide(toEl, bSide, group, toId);

            // Calcula o path curvo que contorna os boxes
            const connection = {
                id: `${item.id}`,
                type: item.type,
                path: this.createCurvedPath(p1, p2, fromTableEl, toTableEl, fromTableId, toTableId),
                p1, p2, aSide, bSide,
                fromId: fromId,
                toId: toId
            };
            this.connections.push(connection);
        });

        console.log('✅ Total connections:', this.connections.length);

        this.renderSvg();
    },
    clearDOMCache() {
        // Limpa o cache de elementos DOM (chamar quando tabelas são adicionadas/removidas)
        this.domElementCache.clear();
        this.anchorCache.clear();
    },
    invalidateBoundingRectCache() {
        // Invalida cache de BoundingRects (posições mudaram)
        this.boundingRectCache.clear();
        this.transformCache = null;
    },
    getCachedBoundingRect(element, cacheKey) {
        // Retorna BoundingRect do cache ou calcula e cacheia
        if (!cacheKey) cacheKey = element.id || `el-${Math.random()}`;

        let cached = this.boundingRectCache.get(cacheKey);
        if (!cached) {
            cached = element.getBoundingClientRect();
            this.boundingRectCache.set(cacheKey, cached);
        }
        return cached;
    },
    getCachedTransform(group) {
        // Calcula e cacheia a matriz de transformação inversa (reutilizado 400x por frame)
        if (!this.transformCache && group) {
            const gCTM = group.getScreenCTM();
            this.transformCache = gCTM ? gCTM.inverse() : null;
        }
        return this.transformCache;
    },
    getPointForSide(boxEl, side, group, cacheKey) {
        const gridSize = this.gridSize;

        // USA CACHE: Pega o rect do cache (economiza ~400 getBoundingClientRect por frame)
        const rect = this.getCachedBoundingRect(boxEl, cacheKey);
        const pt = this.$el.createSVGPoint();

        // Define o ponto no lado correto da box
        pt.x = side === 'right' ? rect.right : rect.left;
        pt.y = rect.top + rect.height / 2;

        // USA CACHE: Reutiliza a matriz de transformação inversa (economiza ~400 cálculos)
        const inverseMatrix = this.getCachedTransform(group);
        const svgP = inverseMatrix ? pt.matrixTransform(inverseMatrix) : pt;

        // Alinha ao grid
        const p = {
            x: Math.round(svgP.x / gridSize) * gridSize,
            y: Math.round(svgP.y / gridSize) * gridSize
        };

        return p;
    },
    updateConnectionAnchorVisibility(fromEl, toEl, fromId, toId) {
        // USA CACHE: Economiza 400 getBoundingClientRect por frame
        const fromRect = this.getCachedBoundingRect(fromEl, fromId);
        const toRect = this.getCachedBoundingRect(toEl, toId);

        const aCenterX = fromRect.left + fromRect.width / 2;
        const bCenterX = toRect.left + toRect.width / 2;

        // Seleciona dinamicamente o lado de conexão de cada box
        // - Se A está à esquerda de B: A sai pela direita; B entra pela esquerda
        // - Se A está à direita de B: A sai pela esquerda; B entra pela direita
        const aSide = aCenterX <= bCenterX ? 'right' : 'left';
        const bSide = aCenterX <= bCenterX ? 'left' : 'right';

        // USA CACHE: Economiza 800 querySelector por frame
        const anchorKeyFrom = `${fromId}-anchors`;
        let fromAnchors = this.anchorCache.get(anchorKeyFrom);
        if (!fromAnchors) {
            fromAnchors = {
                left: fromEl.querySelector('.anchor-left'),
                right: fromEl.querySelector('.anchor-right')
            };
            this.anchorCache.set(anchorKeyFrom, fromAnchors);
        }

        const anchorKeyTo = `${toId}-anchors`;
        let toAnchors = this.anchorCache.get(anchorKeyTo);
        if (!toAnchors) {
            toAnchors = {
                left: toEl.querySelector('.anchor-left'),
                right: toEl.querySelector('.anchor-right')
            };
            this.anchorCache.set(anchorKeyTo, toAnchors);
        }

        if (fromAnchors.left && fromAnchors.right) {
            fromAnchors.left.style.opacity = aSide === 'left' ? '1' : '0';
            fromAnchors.right.style.opacity = aSide === 'right' ? '1' : '0';
        }
        if (toAnchors.left && toAnchors.right) {
            toAnchors.left.style.opacity = bSide === 'left' ? '1' : '0';
            toAnchors.right.style.opacity = bSide === 'right' ? '1' : '0';
        }

        return { aSide, bSide };
    },
    getTableBoundsInSVG(tableEl, group, cacheKey) {
        if (!tableEl || !group) return null;

        // USA CACHE: Economiza 400 getBoundingClientRect por frame
        const rect = this.getCachedBoundingRect(tableEl, cacheKey);

        // Converte os cantos do retângulo para coordenadas SVG
        const pt = this.$el.createSVGPoint();

        // USA CACHE: Reutiliza matriz de transformação inversa
        const inverseMatrix = this.getCachedTransform(group);
        if (!inverseMatrix) return null;

        // Canto superior esquerdo
        pt.x = rect.left;
        pt.y = rect.top;
        const topLeft = pt.matrixTransform(inverseMatrix);

        // Canto inferior direito
        pt.x = rect.right;
        pt.y = rect.bottom;
        const bottomRight = pt.matrixTransform(inverseMatrix);

        return {
            xMin: topLeft.x,
            xMax: bottomRight.x,
            yMin: topLeft.y,
            yMax: bottomRight.y
        };
    },
    createCurvedPath(p1, p2, fromTableEl, toTableEl, fromTableId, toTableId) {
        // USA CACHE: Evita 200 querySelector por frame
        const group = this.linesLayerCache;

        // Obtém os bounds das tabelas uma vez para reutilizar
        let fromTableBounds = null;
        let toTableBounds = null;

        if (fromTableEl && group) {
            fromTableBounds = this.getTableBoundsInSVG(fromTableEl, group, fromTableId);
        }

        if (toTableEl && group) {
            toTableBounds = this.getTableBoundsInSVG(toTableEl, group, toTableId);
        }

        let useVerticalConnection = false;
        let fromCenterX = null;
        let toCenterX = null;
        const directionY = p2.y >= p1.y ? 1 : -1;


        if (fromTableBounds && toTableBounds) {
            fromCenterX = (fromTableBounds.xMin + fromTableBounds.xMax) / 2;
            toCenterX = (toTableBounds.xMin + toTableBounds.xMax) / 2;

            const deltaCenterX = Math.abs(fromCenterX - toCenterX);
            if (deltaCenterX <= 350) {
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

            const pB = { x: exitX, y: p1.y };
            const pC = { x: exitX, y: midY };
            const pD = { x: entryX, y: midY };
            const pE = { x: entryX, y: p2.y };

            const baseRadius = Math.min(12, cellSize / 2);
            const dirHorizontal1 = Math.sign(pB.x - p1.x);
            const dirVertical1 = Math.sign(pC.y - pB.y);
            const dirHorizontal2 = Math.sign(pD.x - pC.x);
            const dirVertical2 = Math.sign(pE.y - pD.y);
            const dirHorizontal3 = Math.sign(p2.x - pE.x);

            const radius1 = Math.min(baseRadius, Math.abs(pB.x - p1.x), Math.abs(pC.y - pB.y));
            const radius2 = Math.min(baseRadius, Math.abs(pC.y - pB.y), Math.abs(pD.x - pC.x));
            const radius3 = Math.min(baseRadius, Math.abs(pD.x - pC.x), Math.abs(pE.y - pD.y));
            const radius4 = Math.min(baseRadius, Math.abs(pE.y - pD.y), Math.abs(p2.x - pE.x));

            let path = `M ${p1.x} ${p1.y}`;
            let currentX = p1.x;
            let currentY = p1.y;

            const lineTo = (x, y) => {
                if (currentX === x && currentY === y) {
                    return;
                }
                path += ` L ${x} ${y}`;
                currentX = x;
                currentY = y;
            };

            const quadTo = (cx, cy, x, y) => {
                if (currentX === x && currentY === y) {
                    return;
                }
                path += ` Q ${cx} ${cy}, ${x} ${y}`;
                currentX = x;
                currentY = y;
            };

            const corner1LineX = radius1 > 0 ? pB.x - dirHorizontal1 * radius1 : pB.x;
            lineTo(corner1LineX, p1.y);
            if (radius1 > 0 && dirVertical1 !== 0) {
                quadTo(pB.x, pB.y, pB.x, pB.y + dirVertical1 * radius1);
            } else {
                lineTo(pB.x, pB.y);
            }

            const corner2LineY = radius2 > 0 && dirVertical1 !== 0 ? pC.y - dirVertical1 * radius2 : pC.y;
            lineTo(pC.x, corner2LineY);
            if (radius2 > 0 && dirHorizontal2 !== 0) {
                quadTo(pC.x, pC.y, pC.x + dirHorizontal2 * radius2, pC.y);
            } else {
                lineTo(pC.x, pC.y);
            }

            const corner3LineX = radius3 > 0 && dirHorizontal2 !== 0 ? pD.x - dirHorizontal2 * radius3 : pD.x;
            lineTo(corner3LineX, pD.y);
            if (radius3 > 0 && dirVertical2 !== 0) {
                quadTo(pD.x, pD.y, pD.x, pD.y + dirVertical2 * radius3);
            } else {
                lineTo(pD.x, pD.y);
            }

            const corner4LineY = radius4 > 0 && dirVertical2 !== 0 ? pE.y - dirVertical2 * radius4 : pE.y;
            lineTo(pE.x, corner4LineY);
            if (radius4 > 0 && dirHorizontal3 !== 0) {
                quadTo(pE.x, pE.y, pE.x + dirHorizontal3 * radius4, pE.y);
            } else {
                lineTo(pE.x, pE.y);
            }

            lineTo(p2.x, p2.y);

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
        const group = svg.querySelector('#lines-layer');

        if (!group) return;

        // Track quais conexões existem agora
        const currentConnectionIds = new Set(this.connections.map(c => c.id));

        // Remove conexões que não existem mais
        const existingGroups = group.querySelectorAll('g[data-connection-id]');
        existingGroups.forEach(g => {
            const id = g.dataset.connectionId;
            if (!currentConnectionIds.has(id)) {
                g.remove();
                this.svgElementCache.delete(id);
            }
        });

        // Atualiza ou cria conexões
        this.connections.forEach(conn => {
            let connGroup = this.svgElementCache.get(conn.id);

            // Se não existe no cache ou não está mais no DOM, cria novo
            if (!connGroup || !group.contains(connGroup)) {
                connGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');
                connGroup.dataset.connectionId = conn.id;
                connGroup.dataset.fromId = conn.fromId;
                connGroup.dataset.toId = conn.toId;
                group.appendChild(connGroup);
                this.svgElementCache.set(conn.id, connGroup);

                // Cria o path principal
                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.classList.add('main-path');
                path.setAttribute('stroke', this.colorLines);
                path.setAttribute('fill', 'none');
                path.setAttribute('stroke-width', '2.2');
                path.setAttribute('pointer-events', 'stroke');
                connGroup.appendChild(path);

                // Adiciona event listeners apenas uma vez
                this.attachConnectionListeners(connGroup, conn);
            } else {
                // Atualiza atributos do grupo existente
                connGroup.dataset.fromId = conn.fromId;
                connGroup.dataset.toId = conn.toId;
            }

            // Atualiza apenas o path (não recria o elemento)
            const path = connGroup.querySelector('.main-path');
            if (path) {
                path.setAttribute('d', conn.path);
            }

            // OTIMIZAÇÃO CRÍTICA: Atualiza decoradores sem remover/recriar
            // Verifica se já existem decoradores e só recria se tipo mudou
            const currentType = connGroup.dataset.connType;
            if (currentType !== conn.type) {
                // Tipo mudou, precisa recriar decoradores
                const existingDecorators = connGroup.querySelectorAll('path:not(.main-path)');
                existingDecorators.forEach(el => el.remove());

                connGroup.dataset.connType = conn.type;
                this.createDecorators(connGroup, conn);
            } else {
                // Tipo não mudou, apenas atualiza posições dos decoradores existentes
                this.updateDecorators(connGroup, conn);
            }
        });

        // Aplica highlight se houver uma tabela destacada
        if (this.highlightedTableId) {
            this.applyHighlightToTable(this.highlightedTableId);
        }
    },
    attachConnectionListeners(connGroup, conn) {
        // Adiciona event listeners apenas uma vez quando a conexão é criada
        const highlightOn = () => {
            connGroup.querySelectorAll('path').forEach(el => {
                el.setAttribute('stroke', '#eab308');
                el.setAttribute('stroke-width', '2.25');
                el.setAttribute('stroke-linecap', 'round');
                el.setAttribute('stroke-linejoin', 'round');
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
                    el.setAttribute('stroke', this.colorLines);
                    el.setAttribute('stroke-width', '2');
                    el.setAttribute('stroke-linecap', 'round');
                    el.setAttribute('stroke-linejoin', 'round');
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
    },
    createDecorators(connGroup, conn) {
        // Cria decoradores pela primeira vez
        const isOneToMany = conn.type === '1:n';
        const isManyToOne = conn.type === 'n:1';
        const isManyToMany = conn.type === 'n:n';

        // Sempre cria barras nas duas pontas
        this.drawBar(connGroup, conn.p1, conn.aSide, 8, 'bar-p1');
        this.drawBar(connGroup, conn.p2, conn.bSide, 8, 'bar-p2');

        if (isManyToOne) {
            this.drawCrowsFoot(connGroup, conn.p1, conn.aSide, 'crow-p1');
            this.drawBar(connGroup, conn.p2, conn.bSide, 12, 'bar-p2-extra');
        } else if (isOneToMany) {
            this.drawCrowsFoot(connGroup, conn.p2, conn.bSide, 'crow-p2');
            this.drawBar(connGroup, conn.p1, conn.aSide, 12, 'bar-p1-extra');
        } else if (isManyToMany) {
            this.drawCrowsFoot(connGroup, conn.p1, conn.aSide, 'crow-p1');
            this.drawCrowsFoot(connGroup, conn.p2, conn.bSide, 'crow-p2');
        } else {
            this.drawBar(connGroup, conn.p2, conn.bSide, 12, 'bar-p2-extra');
            this.drawBar(connGroup, conn.p1, conn.aSide, 12, 'bar-p1-extra');
        }
    },
    updateDecorators(connGroup, conn) {
        // Atualiza posições dos decoradores existentes sem recriar
        const isOneToMany = conn.type === '1:n';
        const isManyToOne = conn.type === 'n:1';
        const isManyToMany = conn.type === 'n:n';

        // Atualiza barras principais
        this.updateBar(connGroup, 'bar-p1', conn.p1, conn.aSide, 8);
        this.updateBar(connGroup, 'bar-p2', conn.p2, conn.bSide, 8);

        if (isManyToOne) {
            this.updateCrowsFoot(connGroup, 'crow-p1', conn.p1, conn.aSide);
            this.updateBar(connGroup, 'bar-p2-extra', conn.p2, conn.bSide, 12);
        } else if (isOneToMany) {
            this.updateCrowsFoot(connGroup, 'crow-p2', conn.p2, conn.bSide);
            this.updateBar(connGroup, 'bar-p1-extra', conn.p1, conn.aSide, 12);
        } else if (isManyToMany) {
            this.updateCrowsFoot(connGroup, 'crow-p1', conn.p1, conn.aSide);
            this.updateCrowsFoot(connGroup, 'crow-p2', conn.p2, conn.bSide);
        } else {
            this.updateBar(connGroup, 'bar-p2-extra', conn.p2, conn.bSide, 12);
            this.updateBar(connGroup, 'bar-p1-extra', conn.p1, conn.aSide, 12);
        }
    },
    updateBar(group, className, endPoint, side, offset = 8) {
        // Atualiza uma barra existente
        const bar = group.querySelector(`.${className}`);
        if (!bar) return;

        const barLen = 7;
        const dir = side === 'left' ? -1 : 1;
        const ox = endPoint.x + dir * offset;
        const oy = endPoint.y;
        const half = barLen / 2;

        bar.setAttribute('d', `M ${ox} ${oy - half} L ${ox} ${oy + half}`);
    },
    updateCrowsFoot(group, className, endPoint, side) {
        // Atualiza crow's foot existente
        const paths = group.querySelectorAll(`.${className}`);
        if (paths.length !== 3) return;

        const dir = side === 'left' ? -1 : 1;
        const hookOffset = 8;
        const length = 14;
        const spreadDeg = 35;

        const ox = endPoint.x + dir * hookOffset;
        const oy = endPoint.y;

        const baseAngle = side === 'right' ? 0 : Math.PI;
        const angles = [
            baseAngle - (Math.PI / 180) * spreadDeg,
            baseAngle,
            baseAngle + (Math.PI / 180) * spreadDeg,
        ];

        paths.forEach((seg, i) => {
            const a = angles[i];
            const x2 = ox - Math.cos(a) * length;
            const y2 = oy - Math.sin(a) * length;
            seg.setAttribute('d', `M ${ox} ${oy} L ${x2} ${y2}`);
        });
    },
    drawBar(group, endPoint, side, offset = 8, className = '') {
        // Pequena barrinha vertical para indicar "1"
        // side: 'left' | 'right'
        const barLen = 7;
        const dir = side === 'left' ? -1 : 1;
        const ox = endPoint.x + dir * offset;
        const oy = endPoint.y;
        const half = barLen / 2;
        const bar = document.createElementNS('http://www.w3.org/2000/svg', 'path');

        bar.setAttribute('d', `M ${ox} ${oy - half} L ${ox} ${oy + half}`);
        bar.setAttribute('stroke', this.colorLines);
        bar.setAttribute('fill', 'none');
        bar.setAttribute('stroke-width', '2');
        bar.setAttribute('stroke-linecap', 'round');
        bar.setAttribute('stroke-linejoin', 'round');
        bar.setAttribute('pointer-events', 'stroke');

        if (className) {
            bar.classList.add(className);
        }

        group.appendChild(bar);
    },
    drawCrowsFoot(group, endPoint, side, className = '') {
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
            seg.setAttribute('stroke', this.colorLines);
            seg.setAttribute('fill', 'none');
            seg.setAttribute('stroke-width', '2');
            seg.setAttribute('pointer-events', 'stroke');
            seg.setAttribute('stroke-linecap', 'round');
            seg.setAttribute('stroke-linejoin', 'round');

            if (className) {
                seg.classList.add(className);
            }

            group.appendChild(seg);
        });
    },
    highlightTableConnections(table) {
        this.clearHighlight();

        table = typeof table === 'number' ? table : table?.id;

        if (!table) {
            this.highlightedTableId = null;
            return;
        }

        this.highlightedTableId = table;
        this.applyHighlightToTable(table);
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
                    el.setAttribute('stroke', this.colorLines);
                    el.setAttribute('stroke-width', '2');
                    el.setAttribute('stroke-linecap', 'round');
                    el.setAttribute('stroke-linejoin', 'round');
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
            el.setAttribute('stroke-linecap', 'round');
            el.setAttribute('stroke-linejoin', 'round');
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
                el.setAttribute('stroke-linecap', 'round');
                el.setAttribute('stroke-linejoin', 'round');
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
