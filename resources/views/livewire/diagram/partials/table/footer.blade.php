<div class="bg-gray-50 dark:bg-zinc-800 flex-none px-3 py-px rounded-b-xl table-footer-beauty">
    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-zinc-400">
        <div class="flex gap-3">
            <span>{{ $table->columns_count }} campos</span>
            <span>•</span>
            <span>{{ $table->primary_count }} PK</span>
            <span>•</span>
            <span>{{ $table->foreign_count }} FK</span>
        </div>
        <button type="button" class="p-1.5 text-gray-400 dark:text-zinc-500 hover:text-gray-600 dark:hover:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-md transition-colors">
            <flux:icon.arrow-down-tray class="size-4" />
        </button>
    </div>
</div>
