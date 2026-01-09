<flux:badge style="background-color: #ffffff; border: 1px solid #e0e0e0;" size="lg" class="text-[10px] dark:text-zinc-500 absolute bottom-5 end-5 z-50 shadow-sm font-bold">
    {{ $this->getTableCount() }} / {{ $this->limit }}
    @if ($this->getTableCount() >= $this->limit)
        <flux:icon.exclamation-triangle class="size-3 ml-1 text-red-500" />
    @endif
</flux:badge>
