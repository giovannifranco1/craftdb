<div
    id="controls"
    class="w-[4rem] py-3 z-20 h-auto bg-white dark:bg-[#0F0F0F] items-center rounded-lg flex flex-col gap-2 shadow-lg border border-gray-200 dark:border-zinc-700"
    x-data="{
        zoomPercent: 75,
        showGrid: true,
        isFullscreen: false,
        grabModeEnabled: false,
        isSpacePressed: false,
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
        toggleGrabMode() {
            this.grabModeEnabled = !this.grabModeEnabled;
            Alpine.raw(this.canvas).setGrabMode(this.grabModeEnabled);

        },
        setCursorMode() {
            this.grabModeEnabled = false;
            Alpine.raw(this.canvas).setGrabMode(false);
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

            // Listener para sincronizar com space key
            window.addEventListener('canvas::space', (e) => {
                this.isSpacePressed = e.detail.isSpacePressed;
                // Se space foi pressionado, mostra visualmente no botão grab
                // Se space foi solto e não está em modo grab permanente, volta ao normal
                if (!this.grabModeEnabled && !e.detail.isSpacePressed) {
                    // Não faz nada, apenas atualiza o estado visual
                }
            });
        }
    }"
    @canvas-zoom.window="zoomPercent = Math.round($event.detail.scale * 100);">

    <!-- Grupo: Adicionar Tabela e Área -->
    <div class="flex flex-col items-center gap-1 pb-2 border-b border-gray-200 dark:border-zinc-700">
        <button
            class="p-2 rounded-md transition-all cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 active:scale-95 group relative text-zinc-700 dark:text-zinc-300"
            type="button"
            title="{{ __('Add Table') }} (T)"
            @click="$dispatch('table::adding')">
            <flux:icon.add-table class="size-5"/>
            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap shadow-lg">
                {{ __('Add Table') }}
            </span>
        </button>

        <button
            class="p-2 rounded-md transition-all cursor-pointer hover:bg-orange-50 dark:hover:bg-orange-900/30 hover:text-orange-600 dark:hover:text-orange-400 active:scale-95 group relative text-zinc-700 dark:text-zinc-300"
            type="button"
            title="{{ __('Add Area') }} (A)"
            @click="$dispatch('area::adding')">
            <flux:icon.add-area class="size-5"/>
            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap shadow-lg">
                {{ __('Add Area') }}
            </span>
        </button>

        <button
            class="p-2 rounded-md transition-all cursor-pointer hover:bg-yellow-50 dark:hover:bg-yellow-900/30 hover:text-yellow-600 dark:hover:text-yellow-400 active:scale-95 group relative text-zinc-700 dark:text-zinc-300"
            type="button"
            title="{{ __('Add Note') }} (N)"
            @click="$dispatch('note::adding')">
            <flux:icon.add-note class="size-5"/>
            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap shadow-lg">
                    {{ __('Add Note') }}
            </span>
        </button>
    </div>

    <!-- Grupo: Zoom -->
    <div class="flex flex-col items-center gap-1 pb-2 border-b border-gray-200 dark:border-zinc-700">
        <button
            class="p-2 rounded-md transition-all cursor-pointer hover:bg-gray-100 dark:hover:bg-zinc-800 active:scale-95 group relative text-zinc-700 dark:text-zinc-300"
            type="button"
            id="zoom-in"
            title="{{ __('Zoom In') }} (Cmd/Ctrl + Scroll)"
            @click="Alpine.raw(canvas).zoom(1.155)">
            <flux:icon.magnifying-glass-plus class="size-5"/>
            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap shadow-lg">
                {{ __('Zoom In') }}
            </span>
        </button>

        <div class="py-1 bg-gray-50 dark:bg-zinc-800 rounded text-xs font-medium text-gray-700 dark:text-zinc-300 min-w-[2.5rem] text-center"
             x-text="`${zoomPercent}%`"
             title="{{ __('Zoom Level') }}">
        </div>

        <button
            class="p-2 rounded-md transition-all cursor-pointer hover:bg-gray-100 dark:hover:bg-zinc-800 active:scale-95 group relative text-zinc-700 dark:text-zinc-300"
            type="button"
            id="zoom-out"
            title="{{ __('Zoom Out') }} (Cmd/Ctrl + Scroll)"
            @click="Alpine.raw(canvas).zoom(0.85)">
            <flux:icon.magnifying-glass-minus class="size-5"/>
            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap shadow-lg">
                {{ __('Zoom Out') }}
            </span>
        </button>

        <button
            class="p-2 rounded-md transition-all cursor-pointer hover:bg-gray-100 dark:hover:bg-zinc-800 active:scale-95 group relative text-zinc-700 dark:text-zinc-300"
            type="button"
            title="{{ __('Exit Fullscreen') }} (F11)"
            @click="toggleFullscreen()">
            <flux:icon.arrows-pointing-out x-show="!isFullscreen" class="size-5"/>
            <flux:icon.arrows-pointing-in x-show="isFullscreen" class="size-5"/>
            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap shadow-lg">
                {{ __('Exit Fullscreen') }}
            </span>
        </button>
    </div>

    <!-- Grupo: Modo de Cursor -->
    <div class="flex flex-col items-center gap-1 pb-2 border-b border-gray-200 dark:border-zinc-700">
        <button
            class="p-2 rounded-md transition-all cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 active:scale-95 group relative text-zinc-700 dark:text-zinc-300"
            :class="(grabModeEnabled || isSpacePressed) ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400' : 'hover:bg-gray-100 dark:hover:bg-zinc-800'"
            type="button"
            title="{{ __('Modo Grab') }} (Pan sem Space)"
            @click="toggleGrabMode()">
            <flux:icon.hand-raised class="size-5"/>
            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap shadow-lg">
                {{ __('Modo Grab') }}
            </span>
        </button>

        <button
            class="p-2 rounded-md transition-all cursor-pointer hover:bg-gray-100 dark:hover:bg-zinc-800 active:scale-95 group relative text-zinc-700 dark:text-zinc-300"
            :class="!grabModeEnabled && !isSpacePressed ? 'bg-gray-100 dark:bg-zinc-800' : ''"
            type="button"
            title="{{ __('Default Cursor') }}"
            @click="setCursorMode()">
            <x-lucide-mouse-pointer-2 class="size-5"/>
            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap shadow-lg">
                {{ __('Cursor Padrão') }}
            </span>
        </button>
    </div>

    <!-- Grupo: Gráfico e Busca -->
    <div class="flex flex-col items-center gap-1">
        <flux:modal.trigger name="diagram-graph">
            <button
                class="p-2 rounded-md transition-all cursor-pointer hover:bg-green-50 dark:hover:bg-green-900/30 hover:text-green-600 dark:hover:text-green-400 active:scale-95 group relative text-zinc-700 dark:text-zinc-300"
                type="button"
                title="{{ __('Graph') }}">
                <flux:icon.graph class="size-5"/>
                <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap shadow-lg">
                    {{ __('Diagram Graph') }}
                </span>
            </button>
        </flux:modal.trigger>

        <flux:modal.trigger name="spotlight" shortcut="cmd.k">
            <button
                class="p-2 rounded-md transition-all cursor-pointer hover:bg-purple-50 dark:hover:bg-purple-900/30 hover:text-purple-600 dark:hover:text-purple-400 active:scale-95 group relative text-zinc-700 dark:text-zinc-300"
                type="button"
                title="{{ __('Search (⌘K)') }}">
                <flux:icon.magnifying-glass class="size-5"/>
                <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap shadow-lg">
                    {{ __('Search (⌘K)') }}
                </span>
            </button>
        </flux:modal.trigger>
    </div>
</div>
