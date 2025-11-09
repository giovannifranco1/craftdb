<div class="absolute z-50 max-w-sm w-full top-12 end-12">
    <div
        x-data="{ message: null }"
        x-show="message"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2"
        x-init="() => {
            @if(session('message'))
                message = @js(session('message'));
                setTimeout(() => { message = null; }, 3000);
            @endif

            Livewire.on('alert', ({ message: msg }) => {
                message = msg;
                setTimeout(() => { message = null; }, 3000);
            })
        }"
        style="display: none"
    >
        <flux:callout variant="success" icon="check-circle">
            <flux:callout.heading x-text="message"></flux:callout.heading>
        </flux:callout>
    </div>
</div>
