<div class="absolute w-96 h-80 select-none bg-white rounded-xl border border-gray-200 shadow-lg"
     x-data="{
        x: {{ $table->x }},
        y: {{ $table->y }},
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
                        <flux:icon.table class="w-3 h-3"/>
                    </div>
                    {{ $table->name }}
                </h3>

                <div class="flex gap-1">
                    <button class="p-1.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-md transition-colors" @mousedown.stop @touchstart.stop>
                        <flux:icon.pencil-square class="size-5"/>
                    </button>
                    <button class="p-1.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-md transition-colors" @mousedown.stop @touchstart.stop>
                        <flux:icon.plus class="size-5"/>
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
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @foreach($this->getColumns() as $column)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-2 px-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 bg-gray-800 rounded text-white text-xs flex items-center justify-center font-bold">
                                        <flux:icon.table-type-primary-key class="w-2 h-2"/>
                                    </div>
                                    <span class="text-xs font-medium text-gray-900">{{ $column->name }}</span>
                                </div>
                            </td>

                            <td class="py-2 px-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs uppercase font-medium bg-gray-100 text-gray-800">{{ $column->type }}</span>
                            </td>

                            <td class="py-2 px-3">
                                @if ($column->is_primary)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">PK</span>
                                @elseif($column->is_foreign)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">FK</span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 border-t border-gray-200 p-3 rounded-b-xl">
            <div class="flex items-center justify-between text-xs text-gray-500">
                <div class="flex gap-3">
                    <span>{{ $table->columns_count }} campos</span>
                    <span>•</span>
                    <span>{{ $table->primary_count }} PK</span>
                    <span>•</span>
                    <span>{{ $table->foreign_count }} FK</span>
                </div>
                <button class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-md transition-colors" @mousedown.stop @touchstart.stop>
                    <flux:icon.arrow-down-tray class="size-4"/>
                </button>
            </div>
        </div>
    </div>
</div>
