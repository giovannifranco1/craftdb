<x-layouts.app :title="__('Dashboard')">
    <div class="relative vh-100 bg-gray-50 p-4">
        <!-- Dashboard Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Diagrama ER - Dashboard</h1>
            <p class="text-gray-600 mt-1">Arraste as tabelas para organizar seu diagrama</p>
        </div>

        <div class="bg-gray-100 p-5 rounded-md">
            <!-- Floating Tables Container -->
            <div
                id="workspace"
                class="relative w-full h-[calc(100vh-13.8rem)] overflow-hidden"
                x-data="{
                    scale: 0.6,
                    minScale: 0.2,
                    maxScale: 2.5,
                    offsetX: 0,
                    offsetY: 0,
                    isPanning: false,
                    startX: 0,
                    startY: 0,
                    init() {
                        // Expor o scale do workspace globalmente para as tabelas
                        window.workspaceScale = this.scale;
                        this.$watch('scale', (value) => {
                            window.workspaceScale = value;
                        });
                    },
                    onWheel(e) {
                        if (window.tableIsDragging) return;
                        if (e.ctrlKey || e.metaKey) {
                            e.preventDefault();
                            const zoomIntensity = 0.05;
                            const direction = e.deltaY > 0 ? -1 : 1;
                            this.scale = Math.min(this.maxScale, Math.max(this.minScale, this.scale + direction * zoomIntensity));
                            return;
                        }
                        if (!e.ctrlKey && !e.metaKey) {
                            this.offsetX -= e.deltaX;
                            this.offsetY -= e.deltaY;
                        }
                    },
                    onKey(e) {
                        if ((e.metaKey || e.ctrlKey) && (e.key === '+' || e.key === '=')) {
                            this.scale = Math.min(this.maxScale, this.scale + 0.1);
                            e.preventDefault();
                        }
                        if ((e.metaKey || e.ctrlKey) && (e.key === '-' || e.key === '_')) {
                            this.scale = Math.max(this.minScale, this.scale - 0.1);
                            e.preventDefault();
                        }
                    },
                    startPan(e) {
                        if (window.tableIsDragging) return;
                        if (e.target.closest('.cursor-move')) return;
                        this.isPanning = true;
                        this.startX = e.clientX - this.offsetX;
                        this.startY = e.clientY - this.offsetY;
                    },
                    pan(e) {
                        if (!this.isPanning) return;
                        this.offsetX = e.clientX - this.startX;
                        this.offsetY = e.clientY - this.startY;
                    },
                    endPan() {
                        this.isPanning = false;
                    }
                }"
                @wheel.prevent="onWheel($event)"
                @keydown.prevent="onKey($event)"
                @mousedown="startPan($event)"
                @mousemove="pan($event)"
                @mouseup="endPan()"
                @mouseleave="endPan()"
                tabindex="0"
            >
            <div class="absolute top-0 left-0 origin-top-left transition-transform duration-100 ease-out"
                :style="`transform: translate(${offsetX}px, ${offsetY}px) scale(${scale});`">
                    @php
                        $total = $itemsCount ?? 9;
                        $maxPerRow = 4;
                        $spacingX = 500;
                        $spacingY = 400;
                    @endphp
                    @foreach(range(1, $total) as $i)
                        @php
                            $row = floor(($i - 1) / $maxPerRow);
                            $col = ($i - 1) % $maxPerRow;
                            mt_srand($i * 2157 + 123);
                            $dx = mt_rand(-22, 22); // desloca X levemente
                            $dy = mt_rand(-20, 20); // desloca Y levemente
                            $x = $col * $spacingX + $dx;
                            $y = $row * $spacingY + $dy;
                        @endphp
                        <livewire:table :x="$x" :y="$y" :key="$i" />
                    @endforeach

                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
