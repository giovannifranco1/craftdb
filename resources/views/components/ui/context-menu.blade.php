<div class="hs-dropdown-menu hidden z-50 min-w-60 bg-white shadow-md rounded-lg mt-2 dark:bg-neutral-800 dark:border dark:border-neutral-700" role="menu" aria-orientation="vertical">
    {{-- Seção: Tabelas --}}
    <div class="p-1 space-y-0.5">
        <button
            type="button"
            @click="Livewire.dispatch('table::add', { x: Math.round(contextMenuPosition.x), y: Math.round(contextMenuPosition.y) })"
            class="w-full flex items-center gap-x-3 py-1.5 px-3 rounded-lg text-[13px] text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700">
            <flux:icon.sheet class="size-4"/>
            <span>Adicionar Tabela</span>
        </button>
    </div>

    {{-- Separador --}}
    <div class="border-t border-gray-200 dark:border-neutral-700 my-1"></div>

    {{-- Seção: Zoom --}}
    <div class="p-1 space-y-0.5">
        <button
            type="button"
            @click="Alpine.raw(canvas).zoom(1.155)"
            class="w-full flex items-center gap-x-3 py-1.5 px-3 rounded-lg text-[13px] text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700">
            <flux:icon.magnifying-glass-plus class="size-4"/>
            <span>Zoom In</span>
        </button>
        <button
            type="button"
            @click="Alpine.raw(canvas).zoom(0.85)"
            class="w-full flex items-center gap-x-3 py-1.5 px-3 rounded-lg text-[13px] text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700">
            <flux:icon.magnifying-glass-minus class="size-4"/>
            <span>Zoom Out</span>
        </button>
        <button
            type="button"
            @click="Alpine.raw(canvas).zoom(1 / Alpine.raw(canvas).getScale()); zoomPercent = 100"
            class="w-full flex items-center gap-x-3 py-1.5 px-3 rounded-lg text-[13px] text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700">
            <flux:icon.arrows-pointing-out class="size-4"/>
            <span>Resetar Zoom</span>
        </button>
    </div>

    {{-- Separador --}}
    <div class="border-t border-gray-200 dark:border-neutral-700 my-1"></div>

    {{-- Seção: Ações --}}
    <div class="p-1 space-y-0.5">
        <button
            type="button"
            @click="$dispatch('save-positions')"
            class="w-full flex items-center gap-x-3 py-1.5 px-3 rounded-lg text-[13px] text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700">
            <flux:icon.arrow-down-tray class="size-4"/>
            <span>Salvar Posições</span>
            <span class="ml-auto text-xs text-gray-500 dark:text-neutral-400">⌘S</span>
        </button>
    </div>
</div>
