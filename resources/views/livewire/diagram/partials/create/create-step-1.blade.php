<div class="space-y-4">

    <flux:field>
        <flux:label badge="Required">{{ __('Diagram Name') }}</flux:label>
        <flux:input placeholder="{{ __('e.g. Product Diagram') }}" class="mt-1" wire:model.lazy="name"/>
        <flux:error name="name" />
    </flux:field>

    <div class="space-y-2">
        <flux:label class="pb-2">{{ __('Style') }}</flux:label>

        <div class="flex flex-col gap-4 grow pt-2 pb-3">
            <label class="relative border rounded-xl p-6 cursor-pointer hover:border-primary-300 hover:bg-primary-50/60 transition flex flex-col @if($style === 'colorful') border-primary-400 bg-primary-50 @else border-zinc-300 bg-white @endif">
                <input
                    type="radio"
                    class="sr-only"
                    wire:model.live="style"
                    value="colorful">
                <div class="flex items-center gap-3 mb-1">
                    <flux:icon.swatch variant="solid" class="size-4" />
                    <span class="text-sm font-semibold">{{ __('Colorful') }}</span>
                </div>
                <p class="text-sm text-zinc-600 leading-relaxed min-h-[55px] max-h-[55px]">
                    {{ __('Displays the diagram with distinct colors for each table and relationship, making it easier to distinguish and focus on specific areas of your model.') }}
                </p>

                <span class="absolute top-2 right-2">
                    @if ($style === 'colorful')
                        <flux:icon.check-circle variant="solid" class="size-5" />
                    @endif
                </span>
            </label>

            <label class="relative border rounded-xl p-6 cursor-pointer hover:border-primary-300 hover:bg-primary-50/60 transition flex flex-col @if($style === 'neutral') border-primary-400 bg-primary-50 @else border-zinc-300 bg-white @endif">
                <input type="radio"
                    class="sr-only"
                    wire:model.live="style"
                    value="neutral">
                <div class="flex items-center gap-3 mb-1">
                    <flux:icon.swatch variant="solid" class="size-4" />
                    <span class="text-sm font-semibold">{{ __('Neutral') }}</span>
                </div>
                <p class="text-sm text-zinc-600 leading-relaxed min-h-[55px] max-h-[55px]">
                    {{ __('Displays the diagram with a single neutral color for a cleaner, distraction-free look.') }}
                </p>

                <span class="absolute top-2 right-2">
                    @if ($style === 'neutral')
                        <flux:icon.check-circle variant="solid" class="size-5" />
                    @endif
                </span>
            </label>
        </div>
    </div>
</div>
