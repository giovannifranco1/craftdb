@props([
    'table' => null
])

<div
    x-data="{
        open: false,

        addRelationshipOneToOne() {
            this.isInRelationshipEvent = true;
            this.relationshipEvent.from_table_id = @js($table->id);
            this.relationshipEvent.type = '1:1';
        },

        addRelationshipOneToMany() {
            this.isInRelationshipEvent = true;
            this.relationshipEvent.from_table_id = @js($table->id);
            this.relationshipEvent.type = '1:n';
        },

        addRelationshipManyToMany() {
            this.isInRelationshipEvent = true;
            this.relationshipEvent.from_table_id = @js($table->id);
            this.relationshipEvent.type = 'n:n';
        },

        addRelationshipManyToOne() {
            this.isInRelationshipEvent = true;
            this.relationshipEvent.from_table_id = @js($table->id);
            this.relationshipEvent.type = 'n:1';
        },

        renameTable() {
            const tableBox = $el.closest('[table-box]');
            $dispatch('table::renaming', { el: tableBox || $el });
        }

    }"
    x-cloak
    x-show="open"
    x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="opacity-0 translate-y-1"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-75"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-1"
    @keydown.escape="isInRelationshipEvent = false; open = false;"
    @mousedown.outside="open = false;"
    @dropdown-menu::opened.window="if (($event.detail?.el || null) !== ($el.closest('[table-box]') || $el)) open = false"
    @dropdown-menu::toggle.window="if (($event.detail?.tableBox || null) === ($el.closest('[table-box]') || $el)) open = !open"
    class="absolute -top-20 -left-71.5 mt-2 min-w-60 w-100 justify-between flex gap-2 items-center text-white rounded-md"
>
    <flux:button.group>

        <flux:button x-data="{ open: false, loadingColor: false }" @mousedown.stop="open = !open" variant="primary" icon:trailing="chevron-down">
            <x-ui.color-circle :color="$table->color"/>
            <x-ui.diagram.table.dropdown-menu-color :table="$table" />
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
