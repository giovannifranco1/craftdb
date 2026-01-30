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

    <div wire:ignore.self class="note-top shadow-md bg-[#fef08a] group relative rounded-tl-xl"></div>

    <!-- Note Content -->
    <div wire:ignore.self class="relative px-3 py-1 top-[24px] shadow-md bg-[#fef08a] flex flex-col rounded-b-xl" :style="`width: ${width}px; height: ${height}px; min-height: 75px;`">
        <textarea
            class="flex-1 w-full font-[Kalam] font-light text-[15px] text-black bg-transparent border-none outline-none shadow-none resize-none placeholder-gray-400 dark:placeholder-zinc-500 leading-relaxed box-border"
            wire:model.live.debounce.350ms="content"
            disabled
            :style="`width: 100%; height: 100%;`"
        ></textarea>
    </div>
</div>
