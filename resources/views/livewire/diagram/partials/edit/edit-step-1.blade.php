<div class="space-y-6">

    <flux:field>
        <flux:label badge="Required">{{ __('Diagram Name') }}</flux:label>
        <flux:input placeholder="{{ __('e.g. Product Diagram') }}" class="mt-1" wire:model.lazy="name"/>
        <flux:error name="name" />
    </flux:field>

    <flux:field>
        <flux:label badge="Optional">{{ __('Description') }}</flux:label>
        <flux:textarea
            placeholder="{{ __('Add a description for your diagram') }}"
            class="mt-1"
            wire:model="description"
            rows="3"
        />
        <flux:error name="description" />
    </flux:field>
</div>
