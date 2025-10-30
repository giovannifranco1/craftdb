<div id="table-{{$table->id}}" table-box x-data="tableInit(@js($table->x), @js($table->y))" :style="`transform: translate3d(${x}px, ${y}px, 0); width: ${getSnappedWidth()}px;`" class="absolute h-full select-none bg-white z-50 rounded-xl border border-gray-200 shadow-lg" :class="`min-h-[${minHeight}px]`">

    <!-- Table -->
    <div class="rounded-xl cursor-pointer w-full h-full flex flex-col">
        <!-- Header -->
        <div :style="`height: ${getGridSize()/2 + getGridSize()}px`" class="bg-gray-50 flex-none border-b border-gray-200 px-3 py-1 rounded-t-xl">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                    <div class="w-4 h-4 bg-gray-600 rounded text-white text-xs flex items-center justify-center font-bold">
                        <flux:icon.table class="w-2 h-2"/>
                    </div>
                    {{ $table->name }}
                </h3>

                <div class="flex gap-1">
                    <button class="p-1.5 cursor-pointer text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-md transition-colors" @mousedown.stop @touchstart.stop>
                        <flux:icon.pencil-square class="size-5"/>
                    </button>
                    {{-- <button class="p-1.5 cursor-pointer text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-md transition-colors" @mousedown.stop @touchstart.stop>
                        <flux:icon.plus-circle class="size-5"/>
                    </button> --}}
                </div>
            </div>
        </div>

        <!-- Table Structure -->
        <div class="flex-auto bg-white">
            <table class="w-full">
                <thead class="bg-gray-50 py-0 sticky top-0">
                    <tr class="border-b py-0 border-gray-200">
                        <th class="text-left py-[4.29px] px-3 text-xs font-medium text-gray-600 uppercase tracking-wider">Campo</th>
                        <th class="text-left px-3 text-xs font-medium text-gray-600 uppercase tracking-wider">Tipo</th>
                        <th class="text-center px-3 text-xs font-medium text-gray-600 uppercase tracking-wider">Chave</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @foreach($this->getColumns() as $column)
                        <tr id="column-{{$table->id }}-{{ $column->id }}" class="hover:bg-gray-50 transition-colors" :style="`height: ${getGridSize()}px`">
                            <td class="py-[2px] px-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 bg-gray-800 rounded text-white text-xs flex items-center justify-center font-bold">
                                        <flux:icon.table-type-primary-key class="w-2 h-2"/>
                                    </div>
                                    <span class="text-xs font-medium text-gray-900">{{ $column->name }}</span>
                                </div>
                            </td>

                            <td>
                                <span class="inline-flex items-center px-2  rounded text-xs uppercase font-medium bg-gray-100 text-gray-800">{{ $column->type }}</span>
                            </td>

                            <td class="text-center">
                                @if ($column->is_primary)
                                    <span class="inline-flex items-center px-2 rounded text-xs font-medium bg-yellow-100 text-yellow-800">PK</span>
                                @elseif($column->is_foreign)
                                    <span class="inline-flex items-center px-2 rounded text-xs font-medium bg-blue-100 text-blue-800">FK</span>
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
        <div class="bg-gray-50 flex-none border-t border-gray-200 px-3 py-[5px] rounded-b-xl">
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

    <script>
        function tableInit(x, y) {
            return {
                x: x,
                y: y,
                isDragging: false,
                startX: 0,
                startY: 0,
                initialX: 0,
                initialY: 0,
                width: 384, // valor inicial (suave para grid 40px)
                minHeight: null,

                init() {
                    // Snap inicial ao grid (caso valores venham desalinhados do backend)
                    this.x = this.snapToGrid(this.x);
                    this.y = this.snapToGrid(this.y);

                    this.$el.style.transform = `translate3d(${this.x}px, ${this.y}px, 0)`;
                    this.$el.style.left = '0px';
                    this.$el.style.top = '0px';
                    this.minHeight = 13 * this.getGridSize();

                    this.$el.addEventListener('mousedown', (e) => this.startDrag(e));
                    document.addEventListener('mousemove', (e) => this.drag(e));
                    document.addEventListener('mouseup', (e) => this.endDrag(e));
                },
                getScale() {
                    return Alpine.raw(this.canvas).getScale();
                },
                getGridSize() {
                    // Tenta pegar do canvas, senão usa 40
                    return Alpine.raw(this.canvas).cellSize || 40;
                },
                getSnappedWidth() {
                    const grid = this.getGridSize();
                    // Múltiplo mais próximo, nunca menos que 1 quadrado
                    return Math.max(grid, Math.round(this.width / grid) * grid);
                },
                snapToGrid(coord) {
                    const gridSize = this.getGridSize();
                    return Math.round(coord / gridSize) * gridSize;
                },
                startDrag(e) {
                    this.isDragging = true;
                    this.startX = e.clientX;
                    this.startY = e.clientY;
                    this.initialX = this.x;
                    this.initialY = this.y;
                    this.$el.style.transition = 'none';
                },
                drag(e) {
                    if (!this.isDragging) return;
                    e.preventDefault();

                    const scale = this.getScale();
                    const dx = (e.clientX - this.startX) / scale;
                    const dy = (e.clientY - this.startY) / scale;

                    // Calcula nova posicao "virtual" prevista
                    let nextX = this.initialX + dx;
                    let nextY = this.initialY + dy;

                    // Snap robusto ao grid
                    const snappedX = this.snapToGrid(nextX);
                    const snappedY = this.snapToGrid(nextY);

                    // Debug: Mostrar valores no console para diagnóstico
                    if (window?.DEBUG_TABLE_DRAG) {
                        console.log('[TableDrag] nextX:', nextX, 'nextY:', nextY, '=> snappedX:', snappedX, 'snappedY:', snappedY);
                    }

                    this.x = snappedX;
                    this.y = snappedY;

                    // Aplica sempre transform (sem left/top)
                    this.$el.style.left = '0px';
                    this.$el.style.top = '0px';
                    this.$el.style.transform = `translate3d(${this.x}px, ${this.y}px, 0)`;
                    this.$el.dispatchEvent(new CustomEvent('box-move', { bubbles: true }));
                },
                endDrag(e) {
                    if (!this.isDragging) return;
                    this.isDragging = false;
                    this.$el.dispatchEvent(new CustomEvent('box-move', { bubbles: true }));
                }
            }
        }
    </script>
</div>
