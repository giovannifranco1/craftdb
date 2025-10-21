<div class="absolute w-96 h-80 select-none bg-white rounded-xl border border-gray-200 shadow-lg"
     x-data="{
        x: {{ $x }},
        y: {{ $y }},
        isDragging: false,
        startX: 0,
        startY: 0,
        initialX: 0,
        initialY: 0,
        ghostX: 0,
        ghostY: 0,
        baseZIndex: 10,
        currentZIndex: 10,
        init() {
            this.$el.addEventListener('selectstart', (e) => e.preventDefault());
            document.addEventListener('mousemove', (e) => this.drag(e));
            document.addEventListener('mouseup', (e) => this.endDrag(e));
            document.addEventListener('touchmove', (e) => this.drag(e), { passive: false });
            document.addEventListener('touchend', (e) => this.endDrag(e));

            // Inicializar z-index global se não existir
            if (!window.draggableElements) {
                window.draggableElements = new Set();
                window.maxZIndex = 1000;
            }

            // Registrar este elemento no sistema global
            window.draggableElements.add(this.$el);
            this.updateAllZIndex();
        },
        startDrag(e) {
            // Permitir drag APENAS no header da tabela
            if (!e.target.closest('.bg-gray-50.border-b.border-gray-200.p-3.rounded-t-xl')) {
                return;
            }
            this.isDragging = true;
            window.tableIsDragging = true;

            // Definir este elemento como o de maior z-index
            this.bringToFront();

            if (e.type === 'touchstart') {
                this.startX = e.touches[0].clientX;
                this.startY = e.touches[0].clientY;
            } else {
                this.startX = e.clientX;
                this.startY = e.clientY;
            }
            this.initialX = this.x;
            this.initialY = this.y;
            this.$el.style.transition = 'none';
            this.ghostX = this.snapCalc(this.x);
            this.ghostY = this.snapCalc(this.y);
        },
        drag(e) {
            if (!this.isDragging) return;

            e.preventDefault();
            let currentX, currentY;
            if (e.type === 'touchmove') {
                currentX = e.touches[0].clientX;
                currentY = e.touches[0].clientY;
            } else {
                currentX = e.clientX;
                currentY = e.clientY;
            }
            // Movimento 1:1 com o mouse, considerando o scale do workspace
            const workspaceScale = window.workspaceScale || 1;
            this.x = this.initialX + ((currentX - this.startX) / workspaceScale);
            this.y = this.initialY + ((currentY - this.startY) / workspaceScale);
            // Ghost preview segue exatamente o mouse (sem snap)
            this.ghostX = this.x;
            this.ghostY = this.y;
        },
        endDrag(e) {
            if (!this.isDragging) return;
            this.isDragging = false;
            window.tableIsDragging = false;
            this.$el.style.transition = 'all 0.2s ease-out';
            this.snapToGrid();

            // Recalcular z-index de todos os elementos após o drag
            this.updateAllZIndex();
        },
        snapToGrid() {
            const gridSize = 20;
            this.x = this.snapCalc(this.x);
            this.y = this.snapCalc(this.y);
        },
        snapCalc(val) {
            const gridSize = 10; // Grid menor para shadow mais próximo
            return Math.round(val / gridSize) * gridSize;
        },
        bringToFront() {
            // Incrementar o z-index máximo e aplicar ao elemento atual
            window.maxZIndex += 1;
            this.currentZIndex = window.maxZIndex;
            this.$el.style.zIndex = this.currentZIndex;
        },
        updateAllZIndex() {
            // Recalcular z-index de todos os elementos draggáveis
            if (!window.draggableElements) return;

            // Ordenar elementos por z-index atual (maior primeiro)
            const elements = Array.from(window.draggableElements).map(el => ({
                element: el,
                zIndex: parseInt(el.style.zIndex) || 10
            })).sort((a, b) => b.zIndex - a.zIndex);

            // Redistribuir z-index começando do maior
            let currentZ = window.maxZIndex || 1000;
            elements.forEach((item, index) => {
                if (item.element === this.$el && this.isDragging) {
                    // Elemento sendo movido mantém o maior z-index
                    item.element.style.zIndex = currentZ;
                    this.currentZIndex = currentZ;
                } else {
                    // Outros elementos recebem z-index decrescente
                    currentZ -= 1;
                    item.element.style.zIndex = currentZ;
                }
            });
        }
     }"
     x-init="init()"
     :style="`transform: translate3d(${x}px, ${y}px, 0); z-index: ${currentZIndex}; transition: ${isDragging ? 'none' : 'all 0.2s ease-out'};`"
     :class="{ 'shadow-2xl': isDragging, 'shadow-lg': !isDragging }"
     @mousedown="startDrag($event)"
     @touchstart="startDrag($event)">


    <!-- Table body -->
    <div class="rounded-xl w-full h-full flex flex-col">
        <!-- Header -->
        <div class="bg-gray-50 border-b border-gray-200 p-3 rounded-t-xl">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                    <div class="w-4 h-4 bg-gray-600 rounded text-white text-xs flex items-center justify-center font-bold">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                        </svg>
                    </div>
                    users
                </h3>

                <div class="flex gap-1">
                    <button class="p-1.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-md transition-colors"
                            @mousedown.stop @touchstart.stop>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button class="p-1.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-md transition-colors" @mousedown.stop @touchstart.stop>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Structure -->
        <div class="overflow-y-auto h-64">
            <table class="w-full">

                <thead class="bg-gray-50 sticky top-0">
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-2 px-3 text-xs font-medium text-gray-600 uppercase tracking-wider">Campo</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-gray-600 uppercase tracking-wider">Tipo</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-gray-600 uppercase tracking-wider">Chave</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-gray-600 uppercase tracking-wider">Relacionamento</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    <!-- id field -->
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-2 px-3">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-gray-800 rounded text-white text-xs flex items-center justify-center font-bold">
                                    <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium text-gray-900">id</span>
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">BIGINT</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">PK</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-xs text-gray-400">-</span>
                        </td>
                    </tr>

                    <!-- name field -->
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-2 px-3">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-gray-600 rounded text-white text-xs flex items-center justify-center font-bold">
                                    <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium text-gray-900">name</span>
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">VARCHAR(255)</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-xs text-gray-400">-</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-xs text-gray-400">-</span>
                        </td>
                    </tr>

                    <!-- email field -->
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-2 px-3">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-gray-600 rounded text-white text-xs flex items-center justify-center font-bold">
                                    <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium text-gray-900">email</span>
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">VARCHAR(255)</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">UK</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-xs text-gray-400">-</span>
                        </td>
                    </tr>

                    <!-- password field -->
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-2 px-3">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-gray-600 rounded text-white text-xs flex items-center justify-center font-bold">
                                    <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium text-gray-900">password</span>
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">VARCHAR(255)</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-xs text-gray-400">-</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-xs text-gray-400">-</span>
                        </td>
                    </tr>

                    <!-- role_id field -->
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-2 px-3">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-gray-700 rounded text-white text-xs flex items-center justify-center font-bold">
                                    <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium text-gray-900">role_id</span>
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">BIGINT</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">FK</span>
                        </td>
                        <td class="py-2 px-3">
                            <div class="text-xs text-gray-600">
                                → roles.id
                            </div>
                        </td>
                    </tr>

                    <!-- created_at field -->
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-2 px-3">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-gray-500 rounded text-white text-xs flex items-center justify-center font-bold">
                                    <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium text-gray-900">created_at</span>
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">TIMESTAMP</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-xs text-gray-400">-</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-xs text-gray-400">-</span>
                        </td>
                    </tr>

                    <!-- updated_at field -->
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-2 px-3">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-gray-500 rounded text-white text-xs flex items-center justify-center font-bold">
                                    <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium text-gray-900">updated_at</span>
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">TIMESTAMP</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-xs text-gray-400">-</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-xs text-gray-400">-</span>
                        </td>
                    </tr>

                    <!-- deleted_at field -->
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-2 px-3">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-gray-500 rounded text-white text-xs flex items-center justify-center font-bold">
                                    <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium text-gray-900">deleted_at</span>
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">TIMESTAMP</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-xs text-gray-400">-</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-xs text-gray-400">-</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 border-t border-gray-200 p-3 rounded-b-xl">
            <div class="flex items-center justify-between text-xs text-gray-500">
                <div class="flex gap-3">
                    <span>8 campos</span>
                    <span>•</span>
                    <span>1 PK</span>
                    <span>•</span>
                    <span>1 FK</span>
                    <span>•</span>
                    <span>1 UK</span>
                </div>
                <button class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-md transition-colors" @mousedown.stop @touchstart.stop>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
