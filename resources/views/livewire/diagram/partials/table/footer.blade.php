<div class="bg-gray-50 flex-none px-3 py-px rounded-b-xl table-footer-beauty">
    <div class="flex items-center justify-between text-xs text-gray-500">
        <div class="flex gap-3">
            <span>{{ $table->columns_count }} campos</span>
            <span>•</span>
            <span>{{ $table->primary_count }} PK</span>
            <span>•</span>
            <span>{{ $table->foreign_count }} FK</span>
        </div>
        <button type="button" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-md transition-colors">
            <flux:icon.arrow-down-tray class="size-4" />
        </button>
    </div>
</div>
