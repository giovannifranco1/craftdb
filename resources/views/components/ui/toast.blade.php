@php($top = request()->is('dashboard', 'settings/*') ? 'top-20' : 'top-34')

<div class="fixed z-50 max-w-sm w-full {{ $top }} end-9">
    <div
        x-data="{
            messageToast: null,
            descriptionToast: null,
            typeToast: 'default',
            progress: 100,
            progressInterval: null,
            timeoutId: null,
            resetTimer() {
                if (this.timeoutId) {
                    clearTimeout(this.timeoutId);
                }
                if (this.progressInterval) {
                    clearInterval(this.progressInterval);
                }
            },
            startProgress() {
                this.resetTimer();

                this.progress = 100;
                const duration = 3000;
                const interval = 50;
                const decrement = (100 / (duration / interval));

                this.progressInterval = setInterval(() => {
                    this.progress -= decrement;
                    if (this.progress <= 0) {
                        this.progress = 0;
                        clearInterval(this.progressInterval);
                        this.progressInterval = null;
                    }
                }, interval);

                this.timeoutId = setTimeout(() => {
                    this.messageToast = null;
                    this.timeoutId = null;
                    setTimeout(() => {
                        this.typeToast = 'success';
                    }, 200);
                }, 3000);
            }
        }"
        x-show="messageToast"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2"
        @alert.window="($event) => {
            messageToast = $event.detail.message;
            descriptionToast = $event.detail.description;
            typeToast = $event.detail.type || 'success';
            startProgress();
        }"
        x-init="() => {
            @if(session('message'))
                messageToast = @js(session('message'));
                typeToast = @js(session('type', 'success'));
                startProgress();
            @endif
        }"
        style="display: none"
        class="relative"
    >
        <!-- Toast de Success -->
        <div x-show="typeToast === 'success'">
            <flux:callout class="dark:bg-[#0F0F0F]" inline variant="success">
                <x-slot name="icon">
                    <flux:icon.check-circle variant="solid" class="size-5" color="green"/>
                </x-slot>

                <flux:callout.heading x-text="messageToast"></flux:callout.heading>
            </flux:callout>

            <!-- Barra de Progresso -->
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-200 dark:bg-zinc-800 rounded-b-lg overflow-hidden">
                <div
                    class="h-full transition-all ease-linear bg-green-500"
                    :style="`width: ${progress}%`"
                ></div>
            </div>
        </div>

        <!-- Toast de Error -->
        <div x-show="typeToast === 'error'">
            <flux:callout inline variant="danger">
                <x-slot name="icon">
                    <flux:icon.x-circle variant="solid" class="size-5" color="red"/>
                </x-slot>

                <flux:callout.heading x-text="messageToast"></flux:callout.heading>
            </flux:callout>

            <!-- Barra de Progresso -->
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-200 dark:bg-zinc-800 rounded-b-lg overflow-hidden">
                <div
                    class="h-full transition-all ease-linear bg-red-500"
                    :style="`width: ${progress}%`"
                ></div>
            </div>
        </div>

        <!-- Toast de Default -->
        <div x-show="typeToast === 'default'">
            <flux:callout class="dark:bg-[#0F0F0F]" inline>
                <x-slot name="icon">
                    <flux:icon.check-circle variant="solid" class="size-5" color="green"/>
                </x-slot>

                <flux:callout.heading x-text="messageToast"></flux:callout.heading>
            </flux:callout>

            <!-- Barra de Progresso -->
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-200 dark:bg-zinc-800 rounded-b-lg overflow-hidden">
                <div
                    class="h-full transition-all ease-linear bg-green-500"
                    :style="`width: ${progress}%`"
                ></div>
            </div>
        </div>

    </div>
</div>
