<x-layouts.app :title="__('Dashboard')">
    <div class="space-y-6">
        <!-- SVG overlay quick test -->
        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">SVG overlay test</h2>
            <div x-data="svgTest()" x-init="init()" class="relative h-64 bg-white rounded-md border border-gray-200">
                <!-- SVG overlay -->
                <svg class="pointer-events-none absolute inset-0 w-full h-full z-10">
                    <path :d="connection.path" stroke="#64748b" stroke-width="2" fill="none" stroke-linecap="round"></path>
                </svg>

                <!-- Box A -->
                <div id="boxA"
                     x-data="{ x: 24, y: 24, isDragging: false, startX: 0, startY: 0, initialX: 0, initialY: 0 }"
                     x-init="
                        $el.addEventListener('mousedown', (e) => startDrag(e));
                        document.addEventListener('mousemove', (e) => drag(e));
                        document.addEventListener('mouseup', (e) => endDrag(e));
                        function startDrag(e) {
                            isDragging = true;
                            startX = e.clientX;
                            startY = e.clientY;
                            initialX = x;
                            initialY = y;
                            $el.style.transition = 'none';
                        }
                        function drag(e) {
                            if (!isDragging) return;
                            e.preventDefault();
                            x = initialX + (e.clientX - startX);
                            y = initialY + (e.clientY - startY);
                            $el.dispatchEvent(new CustomEvent('box-move', { bubbles: true }));
                        }
                        function endDrag(e) {
                            if (!isDragging) return;
                            isDragging = false;
                            $el.dispatchEvent(new CustomEvent('box-move', { bubbles: true }));
                        }
                     "
                     :style="`transform: translate3d(${x}px, ${y}px, 0);`"
                    class="absolute w-40 h-16 bg-sky-100 border border-sky-200 rounded-md shadow-sm flex items-center justify-center cursor-move select-none">
                    <span class="text-xs text-sky-800">Box A</span>
                    <span class="anchor-right absolute right-0 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-sky-500"></span>
                    <span class="anchor-left absolute left-0 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-sky-500"></span>
                </div>

                <!-- Box B -->
                <div id="boxB"
                     x-data="{ x: 200, y: 120, isDragging: false, startX: 0, startY: 0, initialX: 0, initialY: 0 }"
                     x-init="
                        $el.addEventListener('mousedown', (e) => startDrag(e));
                        document.addEventListener('mousemove', (e) => drag(e));
                        document.addEventListener('mouseup', (e) => endDrag(e));
                        function startDrag(e) {
                            isDragging = true;
                            startX = e.clientX;
                            startY = e.clientY;
                            initialX = x;
                            initialY = y;
                            $el.style.transition = 'none';
                        }
                        function drag(e) {
                            if (!isDragging) return;
                            e.preventDefault();
                            x = initialX + (e.clientX - startX);
                            y = initialY + (e.clientY - startY);
                            $el.dispatchEvent(new CustomEvent('box-move', { bubbles: true }));
                        }
                        function endDrag(e) {
                            if (!isDragging) return;
                            isDragging = false;
                            $el.style.transition = 'all 0.2s ease-out';
                            $el.dispatchEvent(new CustomEvent('box-move', { bubbles: true }));
                        }
                     "
                     :style="`transform: translate3d(${x}px, ${y}px, 0);`"
                    class="absolute w-40 h-16 bg-emerald-100 border border-emerald-200 rounded-md shadow-sm flex items-center justify-center cursor-move select-none">
                    <span class="text-xs text-emerald-800">Box B</span>
                    <span class="anchor-right absolute right-0 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="anchor-left absolute left-0 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
            </div>
        </div>

        {{-- <livewire:diagram-list /> --}}
    </div>

    <script>
    function svgTest() {
        return {
            connection: { id: 't-1', path: '' },
            init() {
                let attempts = 0;
                const tryCompute = () => {
                    attempts++;
                    const ok = this.compute();
                    if (!ok && attempts < 8) requestAnimationFrame(tryCompute);
                };
                requestAnimationFrame(tryCompute);

                // Listen for box move events to recompute connection
                this.$el.addEventListener('box-move', () => {
                    this.compute();
                });
            },
            compute() {
                const container = this.$el.getBoundingClientRect();

                const boxA = document.getElementById('boxA');
                const boxB = document.getElementById('boxB');
                if (!boxA || !boxB) return false;

                const aRect = boxA.getBoundingClientRect();
                const bRect = boxB.getBoundingClientRect();

                const aCenterX = aRect.left + aRect.width / 2;
                const bCenterX = bRect.left + bRect.width / 2;

                // Seleciona dinamicamente o lado de conexão de cada box
                // - Se A está à esquerda de B: A sai pela direita; B entra pela esquerda
                // - Se A está à direita de B: A sai pela esquerda; B entra pela direita
                const aSide = aCenterX <= bCenterX ? 'right' : 'left';
                const bSide = aCenterX <= bCenterX ? 'left' : 'right';

                // Alterna visibilidade dos pontinhos de referência
                const aAnchorLeft = boxA.querySelector('.anchor-left');
                const aAnchorRight = boxA.querySelector('.anchor-right');
                const bAnchorLeft = boxB.querySelector('.anchor-left');
                const bAnchorRight = boxB.querySelector('.anchor-right');
                if (aAnchorLeft && aAnchorRight) {
                    aAnchorLeft.style.opacity = aSide === 'left' ? '1' : '0';
                    aAnchorRight.style.opacity = aSide === 'right' ? '1' : '0';
                }
                if (bAnchorLeft && bAnchorRight) {
                    bAnchorLeft.style.opacity = bSide === 'left' ? '1' : '0';
                    bAnchorRight.style.opacity = bSide === 'right' ? '1' : '0';
                }

                const p1 = this.getPointForSide(boxA, aSide, container);
                const p2 = this.getPointForSide(boxB, bSide, container);

                // Calcula o path curvo que contorna os boxes
                this.connection.path = this.createCurvedPath(p1, p2, container);

                return true;
            },
            getPointForSide(boxEl, side, container) {
                const boxRect = boxEl.getBoundingClientRect();
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
                const isBoxALeft = p1.x < p2.x;
                const isBoxAAbove = p1.y < p2.y; // A acima de B

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
                if (isBoxALeft) {
                    // Se A está à esquerda de B, o midX é maior que p1.x
                    midX = p1.x + Math.max(40, deltaX * 0.3);
                } else {
                    // Se A está à direita de B, o midX é menor que p1.x
                    midX = p1.x - Math.max(40, deltaX * 0.3);
                }

                // Garantir que midX não cruze o X de B se deltaX for pequeno
                // Este ajuste ajuda a evitar curvas desnecessárias em proximidade
                if (isBoxALeft && midX > p2.x - curveRadius) {
                    midX = p2.x - curveRadius;
                } else if (!isBoxALeft && midX < p2.x + curveRadius) {
                    midX = p2.x + curveRadius;
                }

                // Se o midX for muito próximo do p1.x, force um valor mínimo
                if (Math.abs(p1.x - midX) < curveRadius * 2) {
                    midX = isBoxALeft ? p1.x + curveRadius * 2 : p1.x - curveRadius * 2;
                }


                let path = `M ${p1.x} ${p1.y}`;

                // A partir de p1, o caminho vai para a horizontal, até midX

                // 1. Segmento Horizontal de p1.x até o início da curva
                const xStartCurve1 = isBoxALeft ? midX - curveRadius : midX + curveRadius;
                path += ` L ${xStartCurve1} ${p1.y}`;

                // 2. Primeira Curva: Horizontal para Vertical (no X=midX)
                // Ponto de Controle (C): midX, p1.y
                // Ponto Final (E): midX, p1.y + curva (para baixo) ou p1.y - curva (para cima)
                const yEndCurve1 = isBoxAAbove ? p1.y + curveRadius : p1.y - curveRadius;
                path += ` Q ${midX} ${p1.y}, ${midX} ${yEndCurve1}`;

                // 3. Segmento Vertical: de yEndCurve1 até o início da Segunda Curva (no X=midX)
                // Ponto de Início da 2ª Curva (no X=midX)
                const yStartCurve2 = isBoxAAbove ? p2.y - curveRadius : p2.y + curveRadius;
                path += ` L ${midX} ${yStartCurve2}`;

                // 4. Segunda Curva: Vertical para Horizontal (no Y=p2.y)
                // Ponto de Controle (C): midX, p2.y
                // Ponto Final (E): p2.x, p2.y
                const xEndCurve2 = isBoxALeft ? midX + curveRadius : midX - curveRadius;
                path += ` Q ${midX} ${p2.y}, ${xEndCurve2} ${p2.y}`;

                // 5. Segmento Final: Horizontal até p2.x
                path += ` L ${p2.x} ${p2.y}`;

                return path;
            }
        }
    }
    </script>
</x-layouts.app>
