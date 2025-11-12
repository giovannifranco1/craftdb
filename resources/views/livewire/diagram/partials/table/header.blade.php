<div
    x-data="{
        loading: false,
        pressed: false,

        eventMouseUp() {
            if (this.pressed) {

                if (this.isInRelationshipEvent) {
                    this.addRelationship();
                    return;
                }

                this.loading = true;
                this.pressed = false;
                $dispatch('table-side-editor::open', { table: @js($table->id) })
            }
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
    @mousedown="pressed = true"
    @mouseup="eventMouseUp()"
    @mouseleave="pressed = false"
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
                        $dispatch('toggle-dropdown-menu', { tableBox: tableBox || $el });
                        $dispatch('dropdown-menu-opened', { el: tableBox || $el });
                    "
                    x-on:keydown.escape="
                        const tableBox = $el.closest('[table-box]');
                        $dispatch('toggle-dropdown-menu', { tableBox: tableBox || $el });
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
