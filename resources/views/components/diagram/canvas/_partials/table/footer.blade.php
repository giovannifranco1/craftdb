<div class="bg-gray-50 dark:bg-background-dark flex-none pl-3 pr-4 h-[36.85px] rounded-b-xl table-footer-beauty">
    <div class="flex items-center my-2 justify-between text-xs font-normal text-gray-500 dark:text-zinc-400">
        <div class="flex gap-3">
            <span>{{ $tableData->columns_count }} {{ __('fields') }}</span>
            <span>•</span>
            <span>{{ $tableData->primary_count }} PK</span>
            <span>•</span>
            <span>{{ $tableData->foreign_count }} FK</span>
        </div>
        {{-- <button type="button" class="py-1.5 text-gray-400 dark:text-zinc-500 hover:text-gray-600 dark:hover:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-md transition-colors">
            <flux:icon.arrow-down-tray class="size-4" />
        </button> --}}
    </div>
</div>
