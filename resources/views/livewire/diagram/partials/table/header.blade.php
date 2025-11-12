<div
    x-data="{
        loading: false,
        mouseDownPos: null,
        clickThreshold: 5, // pixels de tolerância para considerar como clique

        handleMouseDown(e) {
            this.mouseDownPos = {
                x: e.clientX,
                y: e.clientY
            };
        },

        eventMouseUp(e) {
            if (!this.mouseDownPos) return;

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
            $dispatch('table-side-editor::open', { table: @js($table->id) })
        },

        addRelationship() {
            $dispatch('table::add-relationship', {
                fromTableId: this.relationshipEvent.from_table_id,
                toTableId: @js($table->id),
                type: this.relationshipEvent.type,
            });

            this.isInRelationshipEvent = false;
        }

    }"
    @mousedown="handleMouseDown($event)"
    @mouseup="eventMouseUp($event)"
    @mouseleave="mouseDownPos = null"
    @table-side-editor::opened.window="loading = false;"
    :style="`height: ${getGridSize()/2 + getGridSize()}px; cursor: ${loading ? 'wait' : ''};`"
    class="bg-blue-500 flex-none border-b border-gray-200 px-3 py-1 rounded-t-xl table-header-beauty"
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
                    <flux:icon.sheet class="size-3"/>
                </button>
            </div>

            <span class="text-white font-normal text-[12pt] font-[Kalam]">{{ $table->name }}</span>
        </h3>

        <button @mousedown.stop="toggleExpanded($el.closest('[table-box]'))"
            class="p-1.5 text-white hover:text-gray-700 hover:bg-gray-100 rounded-md transition-colors">
            <flux:icon.play variant="solid" x-show="expanded" class="size-4 rotate-90" />
            <flux:icon.play variant="solid" x-show="!expanded" class="size-4" />
        </button>
    </div>
</div>
