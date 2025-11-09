<div class="absolute z-50 max-w-lg w-full top-12 end-12">
    <div
        x-data="{ messageToast: null, descriptionToast: null }"
        x-show="messageToast"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2"
        x-init="() => {
            @if(session('message'))
                messageToast = @js(session('message'));
                setTimeout(() => { messageToast = null; }, 5000);
            @endif

            Livewire.on('alert', ({ message, description }) => {
                messageToast = message;
                description = description;
                setTimeout(() => { messageToast = null; }, 5000);
            })
        }"
        style="display: none"
    >
        <flux:callout inline>
            <x-slot name="icon">
                <flux:icon.check-circle variant="solid" class="size-5" color="green"/>
            </x-slot>

            <flux:callout.heading x-text="messageToast"></flux:callout.heading>

            <x-slot name="actions">
                <flux:button @click="messageToast = null" class="border-0 p-0 shadow-none" icon="x-mark"/>
            </x-slot>
        </flux:callout>
    </div>
</div>
