<div
    x-data="{
        loading: false,
        mouseDownPos: null,
        clickThreshold: 5, // pixels de tolerância para considerar como clique
        isEventRename: false,
        name: $wire.entangle('name'),
        handleMouseDown(e) {
            this.mouseDownPos = {
                x: e.clientX,
                y: e.clientY
            };
        },

        eventMouseUp(e) {
            if (!this.mouseDownPos || Alpine.raw(this.canvas).getGrabMode() || Alpine.raw(this.canvas).getIsSpacePressed()) return;

            // Calcula a distância percorrida desde o mousedown
            const distance = Math.sqrt(
                Math.pow(e.clientX - this.mouseDownPos.x, 2) +
                Math.pow(e.clientY - this.mouseDownPos.y, 2)
            );

            // Se moveu muito, não é um clique - apenas reseta
            if (distance > this.clickThreshold) {
                this.mouseDownPos = null;
                return;
            }

            // É um clique válido
            if (this.isInRelationshipEvent) {
                this.addRelationship();
                this.mouseDownPos = null;
                return;
            }

            this.loading = true;
            this.mouseDownPos = null;
            $dispatch('table-side-editor::open', { tableId: @js($tableData->id) })
        },

        addRelationship() {
            $dispatch('table::add-relationship', {
                fromTableId: this.relationshipEvent.from_table_id,
                toTableId: @js($tableData->id),
                type: this.relationshipEvent.type,
            });

            this.isInRelationshipEvent = false;
        }

    }"
    @mousedown="handleMouseDown($event)"
    @mouseup="eventMouseUp($event)"
    @mouseleave="mouseDownPos = null"
    @table::renaming.window="if (($event.detail?.el || null) === ($el.closest('[table-box]') || $el)) isEventRename = true"
    @table-side-editor::opened.window="loading = false;"
    @canvas::space.window="if ($event.detail.isSpacePressed) $el.style.cursor = 'grab'; else $el.style.cursor = ''"
    :style="`height: ${getGridSize()/2 + getGridSize()}px; cursor: ${loading ? 'wait' : ''};`"
    class="bg-blue-500 dark:bg-blue-600 flex-none border-b border-gray-200 dark:border-zinc-700 px-3 py-1 rounded-t-xl table-header-beauty transition-colors"
>
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
            <div class="relative w-5 h-5 rounded text-white text-xs flex items-center justify-center font-bold table-header-icon">
                <button
                    x-data
                    type="button"
                    class="inline-flex items-center p-2 justify-center"
                    x-on:mousedown.stop="
                        const tableBox = $el.closest('[table-box]');
                        $dispatch('dropdown-menu::toggle', { tableBox: tableBox || $el });
                        $dispatch('dropdown-menu::opened', { el: tableBox || $el });
                    "
                    x-on:keydown.escape="
                        const tableBox = $el.closest('[table-box]');
                        $dispatch('dropdown-menu::toggle', { tableBox: tableBox || $el });
                    "
                >
                    <flux:icon.list-bullet class="size-3.5"/>
                </button>
            </div>


            <div
                wire:loading
                wire:target="renameTable"
                class="animate-spin inline-block size-3 border-3 border-current border-t-transparent text-white rounded-full dark:text-dark-500" role="status" aria-label="loading">
                <span class="sr-only">Loading...</span>
            </div>

            <input
                name="name"
                x-show="isEventRename"
                x-trap="isEventRename"
                x-model="name"
                type="text"
                class="max-w-[14rem] bg-transparent border-none outline-none shadow-none text-white font-normal font-[Kalam] text-[12pt] p-0 m-0 w-[14rem]"
                style="background: transparent !important; box-shadow: none !important;"
                @mousedown.stop
                @keydown.stop
                @keydown.enter="$wire.renameTable()"
                @keydown.escape="isEventRename = false; $wire.resetName()"
                @table::renamed.window="isEventRename = false;"
                @blur="isEventRename = false; $wire.resetName()"
                autocomplete="off"
            />
            <span x-show="!isEventRename" class="text-white font-normal text-[12pt] font-[Kalam] max-w-[14rem] truncate">{{ $tableData->name }}</span>
        </h3>

        <button @mousedown.stop="toggleExpanded($el.closest('[table-box]'))"
            class="py-1.5 px-1 text-white hover:text-white/80 hover:bg-white/10 dark:hover:bg-white/20 rounded-md transition-colors">
            <flux:icon.play variant="solid" x-show="expanded" class="size-4 rotate-90" />
            <flux:icon.play variant="solid" x-show="!expanded" class="size-4" />
        </button>
    </div>
</div>
