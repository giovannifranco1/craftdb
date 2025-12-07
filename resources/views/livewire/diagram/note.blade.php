<div
    x-data="runNote(@js($note->x), @js($note->y), @js($note->width), @js($note->height), @js($note->id))"
    x-show="@js($note->id)"
    id="note-{{ $note->id }}"
    note-id="{{ $note->id }}"
    note-box
    @php
        $noteColor ='#fef08a';
    @endphp
    :style="`transform: translate3d(${x}px, ${y}px, 0);`"
    class="absolute select-none dark:shadow-zinc-900/50"
    >

    <!-- Top (offset to avoid folded corner) -->
    <div
        class="absolute left-0 h-2 cursor-ns-resize z-10 resize-handle"
        data-resize="top"
        @mousedown.stop="startResize('top', $event)"
        style="top: 0; left: 30px; right: 0;"
    ></div>

    <!-- Note Top -->
    <div wire:ignore.self class="note-top shadow-md bg-[#fef08a] group relative">
        <!-- Botão de remover (discreto) -->
        <button
            type="button"
            @mousedown.stop="$wire.remove()"
            wire:loading.attr="disabled"
            class="absolute top-1 left-1.5 z-20 size-4 flex items-center justify-center rounded hover:bg-red-100 dark:hover:bg-red-900/20 transition-all duration-200"
            title="Remover nota"
        >
            <flux:icon.trash class="size-3 text-gray-500 dark:text-zinc-400 hover:text-red-600 dark:hover:text-red-400 transition-colors" />
        </button>
    </div>

    <!-- Note Content -->
    <div wire:ignore.self class="relative px-3 py-1 top-[24px] shadow-md bg-[#fef08a]" :style="`width: ${width}px; height: ${height}px; min-height: 125px;`">
        <div class="flex-1 min-h-0">
            <textarea
                class="w-full h-full font-[Kalam] font-light text-[15px] text-black bg-transparent border-none outline-none shadow-none resize-none placeholder-gray-400 dark:placeholder-zinc-500 leading-relaxed"
                wire:model="content"
                placeholder="Write your note here..."
                style="min-height: 125px;"
                @mousedown.stop
                @keydown.stop
            ></textarea>
        </div>

        <!-- Right -->
        <div
            class="absolute top-0 right-0 bottom-0 w-2 cursor-ew-resize z-10 resize-handle"
            data-resize="right"
            @mousedown.stop="startResize('right', $event)"
        ></div>
        <!-- Bottom -->
        <div
            class="absolute bottom-0 left-0 right-0 h-2 cursor-ns-resize z-10 resize-handle"
            data-resize="bottom"
            @mousedown.stop="startResize('bottom', $event)"
        ></div>
        <!-- Left (offset to avoid folded corner) -->
        <div
            class="absolute top-0 left-0 bottom-0 w-2 cursor-ew-resize z-10 resize-handle"
            data-resize="left"
            @mousedown.stop="startResize('left', $event)"
            style="top: 30px;"
        ></div>
        <!-- Top-Left (offset to avoid folded corner) -->
        <div
            class="absolute w-3 h-3 cursor-nwse-resize z-10 resize-handle"
            data-resize="top-left"
            @mousedown.stop="startResize('top-left', $event)"
            style="top: 30px; left: 30px;"
        ></div>
        <!-- Top-Right -->
        <div
            class="absolute top-0 right-0 w-3 h-3 cursor-nesw-resize z-10 resize-handle"
            data-resize="top-right"
            @mousedown.stop="startResize('top-right', $event)"
        ></div>
        <!-- Bottom-Right -->
        <div
            class="absolute bottom-0 right-0 w-3 h-3 cursor-nwse-resize z-10 resize-handle"
            data-resize="bottom-right"
            @mousedown.stop="startResize('bottom-right', $event)"
        ></div>
        <!-- Bottom-Left -->
        <div
            class="absolute bottom-0 left-0 w-3 h-3 cursor-nesw-resize z-10 resize-handle"
            data-resize="bottom-left"
            @mousedown.stop="startResize('bottom-left', $event)"
        ></div>
    </div>
</div>
