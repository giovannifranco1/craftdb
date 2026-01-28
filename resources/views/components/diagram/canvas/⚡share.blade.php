<?php

use App\Livewire\BaseComponent;
use App\Models\Diagram;
use Livewire\Attributes\Locked;

new class extends BaseComponent {
    #[Locked]
    public Diagram $diagram;

    public bool $showShareModal = false;

    public function mount(Diagram $diagram): void
    {
        $this->diagram = $diagram;
    }

    public function makePublic(): void
    {
        $this->authorize('share', $this->diagram);

        $this->diagram->makePublic();

        $this->alert(message: __('Diagram is now public!'));
        $this->modal('confirm-revoke-or-make-public')->close();

        $this->redirect(route('diagram.show', [$this->diagram->team_id, $this->diagram->slug]), navigate: true);

    }

    public function revokePublic(): void
    {
        $this->authorize('share', $this->diagram);

        $this->diagram->makePrivate();

        $this->alert(message: __('Public access has been revoked.'));
        $this->modal('confirm-revoke-or-make-public')->close();

        $this->redirect(route('diagram.show', [$this->diagram->team_id, $this->diagram->slug]), navigate: true);
    }

    public function copyToClipboard(): void
    {
        $this->alert(message: __('Link copied to clipboard!'), topCenter: true);
    }
}; ?>

<div x-data="{
    publicUrl: @js($diagram->getPublicUrl()),
    copyToClipboard() {
        navigator.clipboard.writeText(this.publicUrl);
        $wire.copyToClipboard();
    }
}" @share::public-url-generated.window="publicUrl = $event.detail.url;" class="px-3 py-2">
    @can('share', $diagram)
        <div class="py-1">
            @if($diagram->isPublic())
                <div class="mb-3">
                    <div class="flex items-center gap-2 mb-2">
                        <flux:icon.globe-alt class="size-4 text-blue-500" />
                        <span class="text-xs font-semibold text-blue-500">{{ __('Public') }}</span>
                    </div>

                    <p class="text-[11px] text-zinc-600 dark:text-zinc-400 mb-3">
                        {{ __('Qualquer pessoa com este link pode visualizar') }}
                    </p>

                    <div class="flex gap-2 mb-3">
                        <input
                            type="text"
                            readonly
                            x-model="publicUrl"
                            @click="$el.select()"
                            class="flex-1 text-[9px] font-[Space_Mono] px-2.5 py-1.5 bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/50 dark:border-zinc-700/50 rounded-md text-zinc-700 dark:text-zinc-300 select-all"
                        >
                        <flux:button
                            size="sm"
                            icon="clipboard"
                            @click="copyToClipboard()"
                        >
                        </flux:button>
                    </div>

                        <flux:button
                            @click="$flux.modal('confirm-revoke-or-make-public').show()"
                            size="sm"
                            type="button"
                            variant="outline"
                            icon="lock-closed"
                            class="w-full"
                        >
                            {{ __('Make Private') }}
                        </flux:button>
                </div>
            @else
                <div class="mb-2">
                    <div class="flex items-center gap-2 mb-2">
                        <flux:icon.lock-closed class="size-4 text-zinc-500" />
                        <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-400">{{ __('Diagrama Privado') }}</span>
                    </div>

                    <p class="text-[11px] text-zinc-600 dark:text-zinc-400 mb-3">
                        {{ __('Apenas membros do time podem visualizar') }}
                    </p>

                    <flux:button
                        @click="$flux.modal('confirm-revoke-or-make-public').show()"
                        type="button"
                        size="sm"
                        variant="primary"
                        color="sky"
                        icon="globe-alt"
                        class="w-full"
                    >
                        {{ __('Make Public') }}
                    </flux:button>
                </div>
            @endif
        </div>

        <flux:modal name="confirm-revoke-or-make-public" :focusable="false" :closable="false" class="md:w-96">
            <div>
                <flux:heading size="lg" class="mb-1">{{ __('Are you sure you want to revoke or make public this diagram?') }}</flux:heading>
            </div>

            <div class="flex gap-2 mt-5">
                <flux:modal.close class="ml-auto">
                    <flux:button icon="x-mark" variant="ghost" type="button">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                @if($diagram->isPublic())
                    <flux:button wire:click="revokePublic" class="cursor-pointer font-medium" variant="danger" icon="trash">{{ __('Revoke Public Access') }}</flux:button>
                @else
                    <flux:button wire:click="makePublic" class="cursor-pointer font-medium" color="sky" variant="primary" icon="globe-alt">{{ __('Make Public') }}</flux:button>
                @endif
            </div>

        </flux:modal>
    @else
        <div class="py-2">
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 text-center">
                {{ __('Você não tem permissão para compartilhar este diagrama') }}
            </p>
        </div>
    @endcan
</div>
