<flux:modal :closable="false" :dismissible="false" name="diagram-edit-{{ $diagram->id }}" class="md:w-150">
    <form wire:submit="update()">
        <div class="flex flex-col flex-1 space-y-6">

            <!-- Header -->
            <div class="space-y-3">
                <flux:heading size="lg" class="flex justify-between">
                    {{ __('Edit Diagram') }}
                    <flux:badge color="primary" size="sm">
                        {{ __('Step :current of :total', ['current' => $step, 'total' => 2]) }}
                    </flux:badge>
                </flux:heading>

                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    {{ __('Update your diagram information.') }}
                </flux:text>
            </div>

            @php
                $steps = "livewire.diagram.partials.edit";

                $STEP_1 = 1;
                $STEP_2 = 2;
                $STEP_FINAL = 2;
            @endphp

            <!-- Steps content -->
            <div class="flex-1">
                @switch($step)
                    @case($STEP_1)
                        @include("$steps.edit-step-1")
                        @break
                    @case($STEP_2)
                        @include("$steps.edit-step-2")
                        @break
                @endswitch
            </div>

            <!-- Footer actions -->
            <div class="flex items-center gap-2 pt-4">
                <flux:button wire:click="cancelEdit()" class="cursor-pointer" variant="ghost" icon="x-mark">{{ __('Cancel') }}</flux:button>

                <flux:spacer />

                @if($step === $STEP_FINAL)
                    <flux:button class="cursor-pointer" variant="subtle" icon="arrow-left" wire:click="prevStep">{{ __('Back') }}</flux:button>
                    <flux:modal.trigger name="confirm-update-diagram">
                        <flux:button
                            class="cursor-pointer"
                            variant="primary"
                            icon="check"
                            type="button"
                            :disabled="!$database"
                        >
                            {{ __('Update') }}
                        </flux:button>
                    </flux:modal.trigger>
                @else
                    <flux:button class="cursor-pointer" variant="primary" icon="arrow-right" wire:click="nextStep">{{ __('Continue') }}</flux:button>
                @endif
            </div>
        </div>

        <flux:modal :closable="false" :dismissible="false" name="confirm-update-diagram" class="min-w-[25rem]">

            @if($showWarning)
                <flux:callout variant="warning" class="mb-4" icon="exclamation-triangle">
                    <flux:callout.heading>{{ __('Warning: Data Loss') }}</flux:callout.heading>
                    <flux:callout.text>
                        {{ __('Changing the style or database type will delete all tables, areas, and notes from this diagram. This action cannot be undone.') }}
                    </flux:callout.text>
                </flux:callout>
            @endif

            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Are you sure you want to update this diagram?') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('This action will update the diagram with the new information.') }}</flux:text>
                </div>
            </div>

            <div class="flex gap-2 mt-4">
                <flux:spacer />

                <flux:button wire:click="cancelEdit()" class="cursor-pointer" variant="ghost" icon="x-mark">{{ __('Cancel') }}</flux:button>
                <flux:button class="cursor-pointer" variant="primary" icon="check" type="submit">{{ __('Update') }}</flux:button>
            </div>
        </flux:modal>
    </form>


</flux:modal>
