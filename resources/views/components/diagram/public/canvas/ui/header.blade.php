<div class="public-header">
    <div class="public-header-content">

        <div class="public-header-title">
            <div class="flex items-center gap-2">
                <div class="flex size-8 shrink-0 items-center justify-start relative">
                    <x-app-logo-icon class="size-full object-contain" />
                </div>
                <h1>{{ $diagram->name }}</h1>
                <span class="text-sm text-gray-400">•</span>
                <span class="text-sm text-gray-400">{{ $diagram->updated_at?->diffForHumans() }}</span>
            </div>
        </div>

        <div>
            <span class="public-badge">
                <flux:icon.globe-alt class="size-4 text-white" />
                {{ __('Visualização Pública') }}
            </span>
        </div>
    </div>
</div>
