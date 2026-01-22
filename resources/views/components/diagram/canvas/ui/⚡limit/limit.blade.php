@php
    $title =  ($this->tableCount >= $this->limit)
        ? __('Table limit reached') . ' - ' . $this->tableCount . ' / ' . $this->limit
        : __('Table limit');
@endphp
<flux:badge title="{{ $title }}" style="background-color: #ffffff; border: 1px solid #e0e0e0;" size="lg" class="text-[10px] dark:text-zinc-500 absolute bottom-5 end-5 z-50 shadow-sm font-bold">
    {{ $this->tableCount }} / {{ $this->limit }}
    @if ($this->tableCount >= $this->limit)
        <flux:icon.exclamation-triangle class="size-3 ml-1 text-red-500" />
    @endif
</flux:badge>
