<div
    id="controls"
    class="w-[4rem] py-3 h-auto bg-white items-center rounded-lg flex flex-col gap-2 shadow-lg border border-gray-200"
    x-data="{
        zoomPercent: 75,
        showGrid: true,
        isFullscreen: false,
        getFullscreenElement() {
            return document.fullscreenElement ||
                   document.webkitFullscreenElement ||
                   document.mozFullScreenElement ||
                   document.msFullscreenElement;
        },
        toggleFullscreen() {
            const isFullscreen = this.getFullscreenElement();

            if (!isFullscreen) {
                const element = document.documentElement;
                const requestFullscreen = element.requestFullscreen ||
                                         element.webkitRequestFullscreen ||
                                         element.mozRequestFullScreen ||
                                         element.msRequestFullscreen;

                if (requestFullscreen) {
                    requestFullscreen.call(element).then(() => {
                        this.isFullscreen = true;
                    }).catch(err => {
                        console.error('Erro ao entrar em fullscreen:', err);
                    });
                }
            } else {
                const exitFullscreen = document.exitFullscreen ||
                                      document.webkitExitFullscreen ||
                                      document.mozCancelFullScreen ||
                                      document.msExitFullscreen;

                if (exitFullscreen) {
                    exitFullscreen.call(document).then(() => {
                        this.isFullscreen = false;
                    }).catch(err => {
                        console.error('Erro ao sair do fullscreen:', err);
                    });
                }
            }
        },
        init() {
            // Listener para detectar mudanças no estado de fullscreen
            const updateFullscreen = () => {
                this.isFullscreen = !!this.getFullscreenElement();
            };

            document.addEventListener('fullscreenchange', updateFullscreen);
            document.addEventListener('webkitfullscreenchange', updateFullscreen);
            document.addEventListener('mozfullscreenchange', updateFullscreen);
            document.addEventListener('MSFullscreenChange', updateFullscreen);

            // Atualiza estado inicial
            this.isFullscreen = !!this.getFullscreenElement();
        }
    }"
    @canvas-zoom.window="zoomPercent = Math.round($event.detail.scale * 100);">

    <!-- Grupo: Adicionar Tabela -->
    <div class="flex flex-col items-center gap-1 pb-2 border-b border-gray-200">
        <button
            class="p-2 rounded-md transition-all cursor-pointer hover:bg-blue-50 hover:text-blue-600 active:scale-95 group relative"
            type="button"
            title="Adicionar Tabela (A)"
            @click="$dispatch('table::adding')">
            <flux:icon.plus class="size-5"/>
            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap">
                Adicionar Tabela
            </span>
        </button>
    </div>

    <!-- Grupo: Zoom -->
    <div class="flex flex-col items-center gap-1 pb-2 border-b border-gray-200">
        <button
            class="p-2 rounded-md transition-all cursor-pointer hover:bg-gray-100 active:scale-95 group relative"
            type="button"
            id="zoom-in"
            title="Zoom In (Cmd/Ctrl + Scroll)"
            @click="Alpine.raw(canvas).zoom(1.155)">
            <flux:icon.magnifying-glass-plus class="size-5"/>
            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap">
                Zoom In
            </span>
        </button>

        <div class="px-2 py-1 bg-gray-50 rounded text-xs font-medium text-gray-700 min-w-[3rem] text-center"
             x-text="`${zoomPercent}%`"
             title="Nível de Zoom">
        </div>

        <button
            class="p-2 rounded-md transition-all cursor-pointer hover:bg-gray-100 active:scale-95 group relative"
            type="button"
            id="zoom-out"
            title="Zoom Out (Cmd/Ctrl + Scroll)"
            @click="Alpine.raw(canvas).zoom(0.85)">
            <flux:icon.magnifying-glass-minus class="size-5"/>
            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap">
                Zoom Out
            </span>
        </button>

        <button
            class="p-2 rounded-md transition-all cursor-pointer hover:bg-gray-100 active:scale-95 group relative"
            type="button"
            :title="isFullscreen ? 'Sair do Fullscreen (F11)' : 'Entrar em Fullscreen (F11)'"
            @click="toggleFullscreen()">
            <flux:icon.arrows-pointing-out x-show="!isFullscreen" class="size-5"/>
            <flux:icon.arrows-pointing-in x-show="isFullscreen" class="size-5"/>
            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap"
                  x-text="isFullscreen ? 'Sair do Fullscreen' : 'Entrar em Fullscreen'">
            </span>
        </button>
    </div>

    <!-- Grupo: Busca -->
    <div class="flex flex-col items-center gap-1">
        <flux:modal.trigger name="spotlight" shortcut="cmd.k">
            <button
                class="p-2 rounded-md transition-all cursor-pointer hover:bg-purple-50 hover:text-purple-600 active:scale-95 group relative"
                type="button"
                title="Buscar (Cmd/Ctrl + K)">
                <flux:icon.magnifying-glass class="size-5"/>
                <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap">
                    Buscar (⌘K)
                </span>
            </button>
        </flux:modal.trigger>
    </div>
</div>
