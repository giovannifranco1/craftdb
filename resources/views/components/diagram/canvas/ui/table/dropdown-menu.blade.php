@props([
    'tableData' => null
])

<div
    x-data="{
        open: false,

        addRelationshipOneToOne() {
            this.isInRelationshipEvent = true;
            this.relationshipEvent.from_table_id = @js($tableData->id);
            this.relationshipEvent.type = '1:1';
        },

        addRelationshipOneToMany() {
            this.isInRelationshipEvent = true;
            this.relationshipEvent.from_table_id = @js($tableData->id);
            this.relationshipEvent.type = '1:n';
        },

        addRelationshipManyToMany() {
            this.isInRelationshipEvent = true;
            this.relationshipEvent.from_table_id = @js($tableData->id);
            this.relationshipEvent.type = 'n:n';
        },

        addRelationshipManyToOne() {
            this.isInRelationshipEvent = true;
            this.relationshipEvent.from_table_id = @js($tableData->id);
            this.relationshipEvent.type = 'n:1';
        },

        renameTable() {
            const tableBox = $el.closest('[table-box]');
            $dispatch('table::renaming', { el: tableBox || $el });
        }

    }"
    x-bind:class="open ? 'opacity-100 pointer-events-auto translate-y-0 duration-150' : 'opacity-0 pointer-events-none -translate-y-1 duration-150'"
    @keydown.escape="isInRelationshipEvent = false; open = false;"
    @mousedown.outside="if (!Alpine.raw(canvas).getIsSpacePressed()) open = false;"
    @dropdown-menu::opened.window="if (($event.detail?.el || null) !== ($el.closest('[table-box]') || $el)) open = false"
    @dropdown-menu::toggle.window="if (($event.detail?.tableBox || null) === ($el.closest('[table-box]') || $el)) open = !open"
    class="absolute -top-20 z-50 -left-71.5 mt-2 min-w-60 w-100 justify-between flex gap-2 items-center text-white rounded-md"
>
    <flux:button.group>

        <flux:button x-data="{ open: false, loadingColor: false }" @mousedown.stop="open = !open" variant="primary" icon:trailing="chevron-down">
            <x-diagram.canvas.ui.color-circle :color="$tableData->color"/>
            <x-diagram.canvas.ui.color-picker :item="$tableData" type="table" />
        </flux:button>

        <flux:button @mousedown.stop="$wire.addColumn()" variant="primary" class="font-[Poppins] text-xs">
            <flux:icon.plus-circle variant="solid" class="size-3.5"/>
            Add Column
        </flux:button>

        <flux:button @mousedown.stop="renameTable()" variant="primary" class="font-[Poppins] text-xs">
            <flux:icon.pencil variant="solid" class="size-3"/>
            Renomear
        </flux:button>

        <flux:button @mousedown.stop="addRelationshipOneToOne()"
            variant="primary"
            class="font-[Space_Mono] text-sm">
            1:1
        </flux:button>

        <flux:button @mousedown.stop="addRelationshipOneToMany()" variant="primary" class="font-[Space_Mono] text-sm">
            1:n
        </flux:button>

        <flux:button @mousedown.stop="addRelationshipManyToOne()" variant="primary" class="font-[Space_Mono] text-sm">
            n:1
        </flux:button>

        <flux:button @mousedown.stop="addRelationshipManyToMany()" variant="primary" class="font-[Space_Mono] text-sm">
            n:n
        </flux:button>

        <flux:button @mousedown.stop="$wire.remove()" variant="primary" square icon="trash" color="red" class="font-[Kalam]"/>
    </flux:button.group>
</div>
