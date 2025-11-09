<div class="absolute z-50 max-w-sm w-full top-12 end-12">
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
                setTimeout(() => { messageToast = null; }, 3000);
            @endif

            Livewire.on('alert', ({ message, description }) => {
                messageToast = message;
                description = description;
                setTimeout(() => { messageToast = null; }, 3000);
            })
        }"
        style="display: none"
    >
        <flux:callout>
            <x-slot name="icon">
                <flux:icon.check-circle variant="solid" class="size-5" color="green"/>
            </x-slot>

            <flux:callout.heading x-text="messageToast"></flux:callout.heading>

            <flux:callout.text x-show="descriptionToast">
                <p>We've improved our notification system to deliver alerts faster and more reliably.</p>
            </flux:callout.text>
        </flux:callout>
    </div>
</div>
