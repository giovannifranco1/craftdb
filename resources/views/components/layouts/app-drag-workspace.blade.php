<x-layouts.app.sidebar :diagram-name="$diagramName ?? ''" :title="$title ?? null">
    <div class="relative vh-100 bg-gray-50">
        <div class="drag-workspace-bg p-5">
            <!-- Floating Tables Container -->
            <div id="workspace"
                class="relative overflow-hidden"
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
                tabindex="0">
                <div class="absolute top-0 left-0 origin-top-left transition-transform duration-100 ease-out" :style="`transform: translate(${offsetX}px, ${offsetY}px) scale(${scale});`">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</x-layouts.app.sidebar>
