@props(['diagram'])
@php
    $team = $diagram->team ?? null;
    $teamId = $diagram->team_id ?? $team?->id;
    $teamName = $team?->name ?? __('Workspace');
    $updatedAt = $diagram->updated_at?->diffForHumans() ?? __('agora mesmo');
@endphp

<div
    x-data="{ visible: true }"
    x-init="visible = localStorage.getItem('canvas-topbar-visible') !== 'false'"
    @topbar-toggled.window="visible = $event.detail.visible"
    class="canvas-topbar pointer-events-none fixed inset-x-0 top-0 z-20 flex justify-center px-9 transition-transform duration-300"
    x-bind:class="visible ? 'pt-5' : '-translate-y-full pt-0'"
>
    <flux:header
        style="padding-right: 1rem; padding-left: 0.5rem;"
        sticky
        class="md:block pointer-events-auto w-full rounded-lg border border-zinc-200/70 bg-white/85 pb-1.5 pt-3 text-sm shadow-sm shadow-zinc-900/5 backdrop-blur-md dark:border-zinc-700 dark:bg-[#0F0F0F] transition-all duration-300"
        x-bind:class="visible ? 'opacity-100' : 'opacity-0'"
    >
        <div class="flex flex-col gap-2">

            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-3">
                    <!-- Logo -->
                    <a href="{{ route('dashboard') }}" class="flex ms-0 items-center rtl:space-x-reverse">
                        <x-app-logo />
                    </a>

                    <flux:separator vertical variant="subtle" class="h-11 mt-1 mx-2"/>

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

                    {{-- Diagram Viewers Avatars --}}
                    <div class="ms-auto">
                        <div class="flex items-center ml-2">
                            @php
                                $colors = array_values(config('color.colorful'));
                                $viewers = $diagram->viewers()->limit(4)->get();
                                $totalViewers = $diagram->viewers()->count();
                                $remainingViewers = max(0, $totalViewers - 4);
                            @endphp
                            @foreach ($viewers as $viewer)
                                <flux:tooltip position="bottom" class="relative flex h-9 w-9 -ml-2.5 first:ml-0 shrink-0 overflow-hidden rounded-full transition-transform hover:scale-110 hover:z-10" :content="$viewer->name . ' • ' . __('Visto') . ' ' . \Carbon\Carbon::parse($viewer->pivot->last_viewed_at)->diffForHumans()">
                                    <span
                                        style="background-color: {{ $colors[$viewer->id % count($colors)] }};"
                                        class="flex h-full w-full items-center justify-center border-2 border-white dark:border-zinc-900 rounded-full text-white text-xs font-medium"
                                    >{{ $viewer->initials() }}</span>
                                </flux:tooltip>
                            @endforeach

                            @if ($remainingViewers > 0)
                                <span class="relative flex h-8 w-8 -ml-2.5 shrink-0 overflow-hidden rounded-lg">
                                    <span class="flex h-full w-full items-center justify-center rounded-full bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                        +{{ $remainingViewers }}
                                    </span>
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Share Button with Dropdown --}}
                    <div>
                        <flux:dropdown>
                            <flux:button class="font-light" size="sm" color="sky" variant="primary" icon="share">
                                {{ __('Compartilhar') }}
                            </flux:button>

                            <flux:menu keep-open class="min-w-80">
                                <livewire:diagram.canvas.share :diagram="$diagram" />

                                <flux:menu.separator />

                                <flux:menu.item icon="users" href="{{ route('team.members.edit') }}" wire:navigate>
                                    {{ __('Invite to team') }}
                                </flux:menu.item>

                                <flux:menu.separator />

                                <flux:menu.item icon="link" x-data="{
                                    copyInternalLink() {
                                        navigator.clipboard.writeText('{{ route('diagram.show', [$diagram->team_id, $diagram->slug]) }}');
                                        Livewire.dispatch('alert', {message: '{{ __('Link copied to clipboard!') }}', type: 'default', topCenter: true});
                                    }
                                }" x-on:click="copyInternalLink()" wire:ignore.self>
                                    {{ __('Copy internal link') }}
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
                                        {{ __('Unique diagram ID') }}
                                    </p>
                                </div>
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    <flux:tooltip position="bottom" :content="$diagram->isFavorite() ? __('Unmark as favorite') : __('Mark as favorite')">
                        <button
                            type="button"
                            x-data
                            x-on:click="toggleFavorite({{ $diagram->isFavorite() }})"
                            class="cursor-pointer p-2 rounded-lg backdrop-blur-sm transition-all duration-300 {{ $diagram->isFavorite() ? 'opacity-100' : '' }}"
                            title="{{ $diagram->isFavorite() ? __('Unmark as favorite') : __('Mark as favorite') }}"
                        >
                            <flux:icon.heart variant="solid" class="size-5 text-red-500 js-icon-favorite {{ $diagram->isFavorite() ? 'block' : 'hidden' }}" />
                            <flux:icon.heart variant="outline" class="size-5 text-zinc-400 dark:text-zinc-500 js-icon-unfavorite {{ $diagram->isFavorite() ? 'hidden' : 'block' }}" />
                        </button>
                    </flux:tooltip>
                          <!-- User Menu -->
                          <flux:dropdown position="bottom" align="end">
                            <flux:profile
                                :initials="auth()->user()->initials()"
                            />

                            <flux:menu class="w-[250px]">
                                <flux:menu.radio.group>
                                    <div class="p-0 text-sm font-normal">
                                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                                <span
                                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                                >
                                                    {{ auth()->user()->initials() }}
                                                </span>
                                            </span>

                                            <div class="grid flex-1 text-start text-sm leading-tight">
                                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </flux:menu.radio.group>

                                <flux:menu.separator/>

                                <livewire:ui.team-switcher/>

                                <flux:menu.radio.group>
                                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                                </flux:menu.radio.group>

                                <flux:menu.separator />

                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                                        {{ __('Log Out') }}
                                    </flux:menu.item>
                                </form>
                            </flux:menu>
                        </flux:dropdown>
                </div>

                {{-- Actions Menu --}}
                <div class="hidden md:flex items-center gap-1.5">
                    {{-- Arquivo Menu --}}
                    <flux:dropdown>
                        <flux:button icon="document-text" variant="ghost" size="xs">
                            {{ __('File') }}
                        </flux:button>

                        <flux:menu class="min-w-52">
                            <flux:menu.item disabled icon="document-duplicate">
                                {{ __('Clone (Coming Soon)') }}
                            </flux:menu.item>

                            <flux:menu.separator />

                            <flux:menu.item disabled icon="arrow-down-tray">
                                {{ __('Export (Coming Soon)') }}
                            </flux:menu.item>

                            <flux:menu.item icon="arrow-up-tray">
                                {{ __('Import (Coming Soon)') }}
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>

                    {{-- Editar Menu --}}
                    <flux:dropdown>
                        <flux:button icon="pencil-square" variant="ghost" size="xs">
                            {{ __('Edit') }}
                        </flux:button>
                        <flux:menu class="min-w-52">
                            <flux:menu.item icon="squares-2x2" x-data x-on:click="Alpine.raw(canvas).organize()">
                                {{ __('Organize automatically') }}
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>

                    {{-- Exibir Menu --}}
                    <flux:dropdown>
                        <flux:button icon="eye" variant="ghost" size="xs">
                            {{ __('Show') }}
                        </flux:button>
                        <flux:menu class="min-w-52">
                            <flux:menu.item x-data x-on:click="Alpine.raw(canvas).zoom(1.155)" icon="plus">
                                {{ __('Increase zoom') }}
                            </flux:menu.item>

                            <flux:menu.item x-data x-on:click="Alpine.raw(canvas).zoom(0.85)" icon="minus">
                                {{ __('Decrease zoom') }}
                            </flux:menu.item>

                            <flux:menu.item x-data x-on:click="Alpine.raw(canvas).zoom(1 / Alpine.raw(canvas).getScale())" icon="magnifying-glass">
                                {{ __('Reset zoom') }}
                            </flux:menu.item>

                            <flux:menu.separator />

                            <flux:menu.item x-data x-on:click="Alpine.raw(canvas).setIsMarkedForDrag(true)" icon="bars-3-bottom-left">
                                {{ __('Show ruler') }}
                            </flux:menu.item>

                            <flux:menu.item x-data x-on:click="Alpine.raw(canvas).setIsMarkedForDrag(false)" icon="x-mark">
                                {{ __('Remove ruler') }}
                            </flux:menu.item>

                            <flux:menu.separator />

                            <flux:menu.item x-data x-on:click="$flux.dark = ! $flux.dark; window.location.reload()" icon="moon">
                                <span x-text="$flux.dark ? '{{ __('Light theme') }}' : '{{ __('Dark theme') }}'"></span>
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>

                    <div
                        x-data="{ updatedAt: @js($updatedAt) }"
                        @table-list::position-updated.window="updatedAt = '{{ __('agora mesmo') }}'"
                        @table::updated.window="updatedAt = '{{ __('agora mesmo') }}'"
                        @table::renamed.window="updatedAt = '{{ __('agora mesmo') }}'"
                        @table::color-updated.window="updatedAt = '{{ __('agora mesmo') }}'"
                        @table::relationship-added.window="updatedAt = '{{ __('agora mesmo') }}'"
                        @table::removed.window="updatedAt = '{{ __('agora mesmo') }}'"
                        @table::column-added.window="updatedAt = '{{ __('agora mesmo') }}'"
                        @table::column-updated.window="updatedAt = '{{ __('agora mesmo') }}'"
                        @table::column-deleted.window="updatedAt = '{{ __('agora mesmo') }}'"
                        @table::move.window="updatedAt = '{{ __('agora mesmo') }}'"
                        @area::updated.window="updatedAt = '{{ __('agora mesmo') }}'"
                        @area::renamed.window="updatedAt = '{{ __('agora mesmo') }}'"
                        @area::color-updated.window="updatedAt = '{{ __('agora mesmo') }}'"
                        @area::relationship-added.window="updatedAt = '{{ __('agora mesmo') }}'"
                        @area::removed.window="updatedAt = '{{ __('agora mesmo') }}'"
                        @area::column-added.window="updatedAt = '{{ __('agora mesmo') }}'"
                        @area::column-updated.window="updatedAt = '{{ __('agora mesmo') }}'"
                        @area::column-deleted.window="updatedAt = '{{ __('agora mesmo') }}'"
                        class="flex flex-wrap items-center gap-3 text-[13px] text-zinc-500 dark:text-zinc-400">
                        <flux:badge size="md" variant="soft" color="emerald" class="text-[10px] dark:bg-emerald-900/40">
                            {{ __('Updated') }}<span class="ms-1" x-text="updatedAt"></span>
                        </flux:badge>
                    </div>
                </div>
            </div>

        </div>
    </flux:header>

    <!-- Toggle Button (attached to topbar) -->
    <button
        @click="visible = !visible; localStorage.setItem('canvas-topbar-visible', visible); $dispatch('topbar-toggled', { visible: visible })"
        x-show="visible"
        x-transition
        class="pointer-events-auto absolute -bottom-8 left-1/2 -translate-x-1/2 rounded-b-lg border border-t-0 border-zinc-200/70 bg-white/95 px-3 py-1 text-zinc-600 shadow-sm backdrop-blur-sm transition-all duration-300 hover:bg-white hover:text-zinc-900 dark:border-zinc-800/70 dark:bg-[#0F0F0F]/95 dark:text-zinc-400 dark:hover:bg-[#0F0F0F] dark:hover:text-zinc-100"
        title="{{ __('Hide topbar') }}"
    >
        <flux:icon.chevron-up class="size-4" />
    </button>
</div>

<!-- Toggle Button (floating when topbar is hidden) -->
<div
    x-data="{ visible: true }"
    x-init="visible = localStorage.getItem('canvas-topbar-visible') !== 'false'"
    @topbar-toggled.window="visible = $event.detail.visible"
    x-show="!visible"
    x-transition
    class="pointer-events-none fixed inset-x-0 top-0 z-20 flex justify-center px-9 pt-3"
>
    <button
        @click="visible = !visible; localStorage.setItem('canvas-topbar-visible', visible); $dispatch('topbar-toggled', { visible: visible })"
        class="pointer-events-auto rounded-b-lg border border-t-0 border-zinc-200/70 bg-white/95 px-3 py-1.5 text-zinc-600 shadow-md backdrop-blur-sm transition-all duration-300 hover:bg-white hover:text-zinc-900 hover:shadow-lg dark:border-zinc-800/70 dark:bg-[#0F0F0F]/95 dark:text-zinc-400 dark:hover:bg-[#0F0F0F] dark:hover:text-zinc-100"
        title="{{ __('Show topbar') }}"
    >
        <flux:icon.chevron-down class="size-4" />
    </button>
</div>

