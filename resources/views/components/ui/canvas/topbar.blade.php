@props(['diagram'])

@php
    $team = $diagram->team ?? null;
    $teamId = $diagram->team_id ?? $team?->id;
    $teamName = $team?->name ?? __('Workspace');
    $updatedAt = $diagram->updated_at?->diffForHumans() ?? __('agora mesmo');
@endphp

<div class="canvas-topbar pointer-events-none fixed inset-x-0 top-0 z-20 flex justify-center px-9 pt-5">
    <flux:header style="padding-right: 1rem; padding-left: 0.5rem;" sticky class="md:block pointer-events-auto w-full rounded-lg border-b border-zinc-200/70 bg-white/85 pb-1.5 pt-3 text-sm shadow-sm shadow-zinc-900/5 backdrop-blur-md dark:border-zinc-800/70 dark:bg-[#0F0F0F]">
        <div class="flex flex-col gap-2">

            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-3">
                    <!-- Logo -->
                    <a href="{{ route('dashboard') }}" class="flex ms-2 items-center space-x-3 rtl:space-x-reverse">
                        <x-app-logo />
                    </a>

                    <flux:separator vertical variant="subtle" class="h-7 mt-1 mx-2"/>

                    {{-- Breadcrumbs --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:breadcrumbs class="flex flex-wrap gap-1 text-[13px] font-medium text-zinc-800 dark:text-zinc-50">
                            <flux:breadcrumbs.item class="text-[11.5px]" href="{{ route('dashboard') }}">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50/70 px-2 py-0.5 text-blue-700 dark:bg-blue-500/10 dark:text-blue-200">
                                    <flux:icon.home-modern class="size-3.5" />
                                    {{ __('Início') }}
                                </span>
                            </flux:breadcrumbs.item>

                            @if ($teamName)
                                <flux:breadcrumbs.item class="text-[11.5px]">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-violet-50/70 px-2 py-0.5 text-violet-700 dark:bg-violet-500/10 dark:text-violet-200">
                                        <flux:icon.users class="size-3.5" />
                                        {{ $teamName }}
                                    </span>
                                </flux:breadcrumbs.item>
                            @endif

                            <flux:breadcrumbs.item class="text-[11.5px]">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50/80 px-2 py-0.5 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200">
                                    <flux:icon.square-3-stack-3d class="size-3.5" />
                                    {{ $diagram->name }}
                                </span>
                            </flux:breadcrumbs.item>
                        </flux:breadcrumbs>
                    </div>

                    {{-- Share Button with Dropdown --}}
                    <div class="ms-auto">
                        <flux:dropdown>
                            <flux:button class="font-light" size="sm" variant="primary" icon="share">
                                {{ __('Compartilhar') }}
                            </flux:button>

                            <flux:menu class="min-w-64">
                                <flux:menu.item icon="link">
                                    {{ __('Copiar link') }}
                                </flux:menu.item>
                                <flux:menu.item icon="qr-code">
                                    {{ __('Gerar QR Code') }}
                                </flux:menu.item>

                                <flux:menu.separator />

                                <flux:menu.item icon="envelope">
                                    {{ __('Compartilhar por email') }}
                                </flux:menu.item>
                                <flux:menu.item icon="users">
                                    {{ __('Convidar para o time') }}
                                </flux:menu.item>

                                <flux:menu.separator />

                                <flux:menu.item icon="globe-alt">
                                    {{ __('Tornar público') }}
                                </flux:menu.item>

                                <flux:menu.separator />

                                <div class="px-3 py-2">
                                    <div class="flex items-center gap-2 rounded-md px-2.5 py-1.5 bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/50 dark:border-zinc-700/50">
                                        <flux:icon.hashtag class="size-3 text-zinc-400 dark:text-zinc-500 flex-shrink-0" />
                                        <code class="text-[10px] text-zinc-600 dark:text-zinc-400 font-mono select-all flex-1 truncate">
                                            {{ $diagram->slug }}
                                        </code>
                                    </div>
                                    <p class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-1.5 text-center">
                                        {{ __('ID único do diagrama') }}
                                    </p>
                                </div>
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    {{-- User Menu --}}
                    <div>
                        <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                            <span class="flex h-full w-full items-center justify-center rounded-full bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                {{ auth()->user()->initials() }}
                            </span>
                        </span>
                    </div>
                </div>

                {{-- Actions Menu --}}
                <div class="hidden md:flex items-center gap-1.5">
                    {{-- Arquivo Menu --}}
                    <flux:dropdown>
                        <flux:button icon="document-text" variant="ghost" size="xs">
                            {{ __('Arquivo') }}
                        </flux:button>

                        <flux:menu class="min-w-52">
                            <flux:menu.item disabled icon="document-duplicate">
                                {{ __('Duplicar diagrama') }} (Em breve)
                            </flux:menu.item>

                            <flux:menu.separator />

                            <flux:menu.item disabled icon="arrow-down-tray">
                                {{ __('Exportar') }} (Em breve)
                            </flux:menu.item>

                            <flux:menu.item icon="arrow-up-tray">
                                {{ __('Importar') }}
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>

                    {{-- Editar Menu --}}
                    <flux:dropdown>
                        <flux:button icon="pencil-square" variant="ghost" size="xs">
                            {{ __('Editar') }}
                        </flux:button>
                        <flux:menu class="min-w-52">
                            <flux:menu.item icon="squares-2x2" x-data x-on:click="Alpine.raw(canvas).organize()">
                                {{ __('Organizar automaticamente') }}
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>

                    {{-- Exibir Menu --}}
                    <flux:dropdown>
                        <flux:button icon="eye" variant="ghost" size="xs">
                            {{ __('Exibir') }}
                        </flux:button>
                        <flux:menu class="min-w-52">
                            <flux:menu.item x-data x-on:click="Alpine.raw(canvas).zoom(1.155)" icon="plus">
                                {{ __('Aumentar zoom') }}
                            </flux:menu.item>

                            <flux:menu.item x-data x-on:click="Alpine.raw(canvas).zoom(0.85)" icon="minus">
                                {{ __('Diminuir zoom') }}
                            </flux:menu.item>

                            <flux:menu.item x-data x-on:click="Alpine.raw(canvas).zoom(1 / Alpine.raw(canvas).getScale())" icon="magnifying-glass">
                                {{ __('Resetar zoom') }}
                            </flux:menu.item>

                            <flux:menu.separator />

                            <flux:menu.item x-data x-on:click="Alpine.raw(canvas).setIsMarkedForDrag(true)" icon="bars-3-bottom-left">
                                {{ __('Mostrar régua') }}
                            </flux:menu.item>

                            <flux:menu.item x-data x-on:click="Alpine.raw(canvas).setIsMarkedForDrag(false)" icon="x-mark">
                                {{ __('Remover régua') }}
                            </flux:menu.item>

                            <flux:menu.separator />

                            <flux:menu.item x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon">
                                {{ __('Tema escuro') }}
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>

                    <div x-data="{ updatedAt: @js($updatedAt) }" @table-list::position-updated.window="updatedAt = '{{ __('agora mesmo') }}'" class="flex flex-wrap items-center gap-3 text-[13px] text-zinc-500 dark:text-zinc-400">
                        <flux:badge size="sm" variant="soft" color="emerald" class="text-[10px] dark:bg-emerald-900/40">
                            {{ __('Updated') }}<span class="ms-1" x-text="updatedAt"></span>
                        </flux:badge>
                    </div>
                </div>
            </div>

        </div>
    </flux:header>
</div>

