<div class="absolute z-50 max-w-sm w-full top-12 end-12">
    <div
        x-data="{
            messageToast: null,
            descriptionToast: null,
            progress: 100,
            progressInterval: null,
            timeoutId: null,
            resetTimer() {
                // Limpa timeout anterior se existir
                if (this.timeoutId) {
                    clearTimeout(this.timeoutId);
                }
                // Limpa intervalo anterior se existir
                if (this.progressInterval) {
                    clearInterval(this.progressInterval);
                }
            },
            startProgress() {
                // Reseta timers anteriores
                this.resetTimer();

                // Reseta o progresso
                this.progress = 100;
                const duration = 5000; // 5 segundos
                const interval = 50; // atualiza a cada 50ms
                const decrement = (100 / (duration / interval));

                // Inicia o intervalo de progresso
                this.progressInterval = setInterval(() => {
                    this.progress -= decrement;
                    if (this.progress <= 0) {
                        this.progress = 0;
                        clearInterval(this.progressInterval);
                        this.progressInterval = null;
                    }
                }, interval);

                // Define o timeout para esconder o toast
                this.timeoutId = setTimeout(() => {
                    this.messageToast = null;
                    this.timeoutId = null;
                }, 5000);
            }
        }"
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
                startProgress();
            @endif

            Livewire.on('alert', ({ message, description }) => {
                messageToast = message;
                descriptionToast = description;
                startProgress();
            })
        }"
        style="display: none"
        class="relative"
    >
        <flux:callout inline>
            <x-slot name="icon">
                <flux:icon.check-circle variant="solid" class="size-5" color="green"/>
            </x-slot>

            <flux:callout.heading x-text="messageToast"></flux:callout.heading>
        </flux:callout>

        <!-- Barra de Progresso -->
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-200 rounded-b-lg overflow-hidden">
            <div
                class="h-full bg-green-500 transition-all ease-linear"
                :style="`width: ${progress}%`"
            ></div>
        </div>
    </div>
</div>
