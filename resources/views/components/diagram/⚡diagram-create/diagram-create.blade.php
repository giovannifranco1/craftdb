<flux:modal :closable="false" :dismissible="false" name="diagram-create" class="md:w-150 min-h-[65%]">
    <form wire:submit="create()">
        <div class="space-y-6 flex flex-col">

            <!-- Header -->
            <div class="space-y-3">
                <flux:heading size="lg" class="flex justify-between">
                    {{ __('New Diagram') }}
                    <flux:badge color="primary" size="sm">
                        {{ __('Step :current of :total', ['current' => $step, 'total' => 2]) }}
                    </flux:badge>
                </flux:heading>

                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    {{ __('Import in two simple steps.') }}
                </flux:text>
            </div>

            <div wire:transition>
                @php
                    $steps = "components.diagram._partials.create";

                    $STEP_1 = 1;
                    $STEP_2 = 2;
                    $STEP_FINAL = 2;
                @endphp

                <!-- Steps content -->
                @switch($step)
                    @case($STEP_1)
                        @include("$steps.create-step-1")
                        @break
                    @case($STEP_2)
                        @include("$steps.create-step-2")
                        @break
                @endswitch
            </div>

            <!-- Footer actions -->
            <div class="flex items-center gap-2">
                <flux:button wire:click="cancelCreate()" class="cursor-pointer" variant="ghost" icon="x-mark">{{ __('Cancel') }}</flux:button>

                <flux:spacer />

                @if($step === $STEP_FINAL)
                    <flux:button class="cursor-pointer" variant="subtle" icon="arrow-left" wire:click="prevStep">{{ __('Back') }}</flux:button>
                    <flux:button
                        class="cursor-pointer"
                        variant="primary"
                        icon="check"
                        type="submit"
                        :disabled="!$database"
                    >
                        {{ __('Create') }}
                    </flux:button>
                @else
                    <flux:button class="cursor-pointer" variant="primary" icon="arrow-right" wire:click="nextStep">{{ __('Continue') }}</flux:button>
                @endif
            </div>
        </div>
    </form>

</flux:modal>
