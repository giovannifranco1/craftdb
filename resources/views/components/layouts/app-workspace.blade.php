<x-layouts.app.master :diagram="$diagram" :title="$title ?? null">

    <div id="workspace" x-data="workspaceInit()" class="relative overflow-hidden drag-workspace-bg w-full h-[calc(100vh-7rem)]">
        <!-- SVG overlay - movido para fora do workspace-inner -->
        <svg id="svg-overlay" x-data="svgInit()" class="pointer-events-none absolute inset-0 w-full h-full"></svg>

        <div id="workspace-outer" x-data="offsetInit()" class="absolute inset-0 cursor-grab active:cursor-grabbing">
            <div id="workspace-inner" style="will-change: transform;">
                {{ $slot }}
            </div>
        </div>
    </div>

    <script>
        function workspaceInit() {
            return {
                connections: [{ id: 't-1', path: '' }],
                workspaceOffset: { x: 0, y: 0 },
                workspaceScale: 1,
                relationships: @js($relationships),

                init() {
                    this.$el.addEventListener('box-move', () => this.compute());

                    this.$el.addEventListener('workspace-move', (e) => {
                        const transform = e.target.style.transform.match(/translate\(([-0-9.]+)px,\s*([-0-9.]+)px\)/);
                        if (transform) {
                            this.workspaceOffset.x = parseFloat(transform[1]) || 0;
                            this.workspaceOffset.y = parseFloat(transform[2]) || 0;
                        }
                        this.compute();
                    });

                    this.$el.addEventListener('workspace-zoom', (e) => {
                        this.workspaceScale = e.detail.scale || 1;
                        this.compute();
                    });

                    requestAnimationFrame(() => this.compute());

                },
                compute() {
                    this.connections = [];

                    const container = this.$el.getBoundingClientRect();

                    this.relationships.forEach(item => {
                        const fromEl = document.getElementById(`column-${item.from_table_id}-${item.from_column_id}`);
                        const toEl = document.getElementById(`column-${item.to_table_id}-${item.to_column_id}`);

                        if (!fromEl || !toEl) {
                            return false;
                        }

                        const aRect = fromEl.getBoundingClientRect();
                        const bRect = toEl.getBoundingClientRect();

                        const aCenterX = aRect.left + aRect.width / 2;
                        const bCenterX = bRect.left + bRect.width / 2;

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

                        const p1 = this.getPointForSide(fromEl, aSide, container);
                        const p2 = this.getPointForSide(toEl, bSide, container);

                        // Calcula o path curvo que contorna os boxes
                        const connection = { id: `${item.id}`, type: item.type, path: this.createCurvedPath(p1, p2, container) };
                        this.connections.push(connection);
                    });

                    console.log('Total connections:', this.connections.length);
                    this.renderSvg();

                    return true;
                },
                getPointForSide(boxEl, side, container) {
                    const boxRect = boxEl.getBoundingClientRect();

                    // Como o SVG agora está fora do workspace-inner, não precisa compensar transformações
                    const anchorX = side === 'right'
                        ? (boxRect.right - container.left)
                        : (boxRect.left - container.left);

                    const anchorY = boxRect.top - container.top + boxRect.height / 2;

                    return { x: anchorX, y: anchorY };
                },
                createCurvedPath(p1, p2, container) {
                    // Calcula distâncias
                    const deltaX = Math.abs(p2.x - p1.x);
                    const deltaY = Math.abs(p2.y - p1.y);

                    // Determina a direção da conexão
                    const isfromElLeft = p1.x < p2.x;
                    const isfromElAbove = p1.y < p2.y; // A acima de B

                    // Raio das curvas nos cantos
                    const curveRadius = 8;

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
                        midX = p1.x + Math.max(40, deltaX * 0.3);
                    } else {
                        // Se A está à direita de B, o midX é menor que p1.x
                        midX = p1.x - Math.max(40, deltaX * 0.3);
                    }

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
                    const svg = document.getElementById('svg-overlay');

                    if (!svg) {
                        return;
                    }

                    svg.innerHTML = '';

                    this.connections.forEach(connection => {
                        console.log('Creating path for connection:', connection);

                        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                        path.setAttribute('data-connection-id', connection.id);
                        path.setAttribute('d', connection.path);
                        path.setAttribute('stroke', '#64748b');
                        path.setAttribute('stroke-width', '2');
                        path.setAttribute('fill', 'none');
                        path.setAttribute('stroke-linecap', 'round');

                        svg.appendChild(path);
                        console.log('Path added to SVG:', path);
                    });

                    console.log('SVG children count:', svg.children.length);
                }
            }
        }
        function offsetInit() {
            return {
                offsetX: 0,
                offsetY: 0,
                scale: 0.5,
                isDragging: false,
                startX: 0,
                startY: 0,
                inner: null,

                init() {
                    this.inner = document.getElementById('workspace-inner');

                    this.$el.addEventListener('mousedown', (e) => {
                        if (e.target.closest('[table-box]')) return; // ignora elementos com atributo table-box

                        this.isDragging = true;
                        this.startX = e.clientX - this.offsetX;
                        this.startY = e.clientY - this.offsetY;
                    });

                    document.addEventListener('mousemove', (e) => {
                        if (!this.isDragging) return;
                        this.offsetX = e.clientX - this.startX;
                        this.offsetY = e.clientY - this.startY;

                        this.applyTransform();
                        this.$el.dispatchEvent(new CustomEvent('workspace-move', { bubbles: true }));
                    });

                    window.addEventListener('keydown', (e) => {
                        if (!e.metaKey) return; // só com CMD

                        if (e.key === '+' || e.key === '=') {
                            e.preventDefault();
                            this.scale = Math.min(this.scale + 0.1, 2); // máximo 200%
                            this.applyTransform();
                            this.$el.dispatchEvent(new CustomEvent('workspace-zoom', { bubbles: true, detail: { scale: this.scale } }));
                        } else if (e.key === '-') {
                            e.preventDefault();
                            this.scale = Math.max(this.scale - 0.1, 0.4); // mínimo 40%
                            this.applyTransform();
                            this.$el.dispatchEvent(new CustomEvent('workspace-zoom', { bubbles: true, detail: { scale: this.scale } }));
                        } else if (e.key === '0') {
                            e.preventDefault();
                            this.scale = 1;
                            this.offsetX = this.offsetY = 0;
                            this.applyTransform();
                            this.$el.dispatchEvent(new CustomEvent('workspace-zoom', { bubbles: true, detail: { scale: this.scale } }));
                        }
                    });

                    document.addEventListener('mouseup', () => { this.isDragging = false; });

                    this.applyTransform();
                },

                applyTransform() {
                    this.inner.style.transform = `translate(${this.offsetX}px, ${this.offsetY}px) scale(${this.scale})`;
                    this.inner.style.transformOrigin = '0 0';
                }
            }
        }
        function svgInit() {
            return {
                init() {
                    document.addEventListener('computed', () => console.log(22));
                }
            }
        }
    </script>

</x-layouts.app.master>
