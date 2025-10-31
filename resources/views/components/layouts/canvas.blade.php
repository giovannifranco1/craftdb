<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head-canvas')
    </head>

    @use('App\Models\Table')

    <body>
        <div x-data="{canvas: new InfiniteCanvas(25), zoomPercent: 75}" class="container">
            <canvas id="canvas"></canvas>

            <svg id="svg-overlay" x-data="svg()">
                <g id="lines-layer"></g>
            </svg>

            <div class="tables">
                {{ $slot }}
            </div>

            <div id="controls" class="w-[20rem] h-14 bg-white items-center rounded-lg flex justify-between">
                <button class="p-1 ms-2 rounded-sm transition-colors cursor-pointer hover:bg-gray-200" type="button" id="zoom-in" @click="Alpine.raw(canvas).zoom(1.155)">
                    <flux:icon.magnifying-glass-plus class="size-6"/>
                </button>

                <span @canvas-zoom.window="zoomPercent = Math.round($event.detail.scale * 100);" x-text="`${zoomPercent}%`"></span>

                <button class="p-1 me-2 rounded-sm transition-colors cursor-pointer hover:bg-gray-200" type="button" id="zoom-in" @click="Alpine.raw(canvas).zoom(0.85)">
                    <flux:icon.magnifying-glass-minus class="size-6"/>
                </button>
            </div>
        </div>

        @fluxScripts
    </body>

    <script>
        function svg() {
            return {
                connections: [{ id: 't-1', path: '' }],
                scale: 1,
                relationships: @js($relationships),
                computeTimeout: null,
                gridSize: null, // Mesmo tamanho do grid do InfiniteCanvas
                linesLayer: null,
                initialTransform: null,

                init() {
                    this.gridSize = Alpine.raw(this.canvas).cellSize;
                    this.linesLayer = this.$el.querySelector('#lines-layer'),

                    // Inicializa o transform do <g> para manter sync com o canvas
                    this.linesLayer.setAttribute('transform', Alpine.raw(this.canvas).getTransformString());

                    // Aguarda o DOM estar completamente renderizado
                    requestAnimationFrame(() => {this.compute()});

                    window.addEventListener('canvas-zoom', (e) => {
                        // Aplica o mesmo transform do canvas ao <g>
                        // Não recomputa aqui para evitar "pulos"; as paths acompanham via transform
                        const transform = Alpine.raw(this.canvas).getTransformString();
                        this.linesLayer.setAttribute('transform', transform);
                    });

                    window.addEventListener('canvas-transform', (e) => {
                        this.linesLayer.setAttribute('transform', e.detail.transform);
                    });

                    window.addEventListener('box-move', () => {
                        this.debouncedCompute();
                    });

                    window.addEventListener('table-expanded' , e => {
                        console.log(e.detail);
                        this.compute();
                    })

                    window.addEventListener('resize', () => {
                        this.compute();
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

                    const svg = this.$el.getBoundingClientRect();
                    const group = this.$el.querySelector('#lines-layer');

                    this.relationships.forEach(item => {
                        const fromEl = document.getElementById(`column-${item.from_table_id}-${item.from_column_id}`);
                        const toEl = document.getElementById(`column-${item.to_table_id}-${item.to_column_id}`);

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
                            path: this.createCurvedPath(p1, p2),
                            p1, p2, aSide, bSide,
                            fromId: `column-${item.from_table_id}-${item.from_column_id}`,
                            toId: `column-${item.to_table_id}-${item.to_column_id}`
                        };
                        this.connections.push(connection);
                    });

                    console.log('Total connections:', this.connections.length);

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
                createCurvedPath(p1, p2) {
                    // Calcula distâncias
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
                            connGroup.querySelectorAll('path').forEach(el => {
                                el.setAttribute('stroke', '#888');
                                el.setAttribute('stroke-width', '2');
                            });
                            const fe = document.getElementById(conn.fromId);
                            const te = document.getElementById(conn.toId);
                            [fe, te].forEach(node => {
                                if (!node) return;
                                node.style.backgroundColor = '';
                                node.style.outline = '';
                                node.style.borderRadius = '';
                            });
                        };

                        connGroup.addEventListener('mouseenter', highlightOn);
                        connGroup.addEventListener('mouseleave', highlightOff);
                    });

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
                    const spreadDeg = 30; // abertura das barras externas

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
                }
            }
        }
    </script>
</html>


