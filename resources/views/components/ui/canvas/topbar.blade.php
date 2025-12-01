@props(['diagram'])

@php
    $team = $diagram->team ?? null;
    $teamId = $diagram->team_id ?? $team?->id;
    $teamName = $team?->name ?? __('Workspace');
    $updatedAt = $diagram->updated_at?->diffForHumans() ?? __('agora mesmo');
@endphp

<div class="canvas-topbar pointer-events-none fixed inset-x-0 top-0 z-10 flex justify-center px-9 pt-5">
    <flux:header sticky class="pointer-events-auto w-full rounded-lg border-b border-zinc-200/70 bg-white/85 py-1.5 text-sm shadow-sm shadow-zinc-900/5 backdrop-blur-md dark:border-zinc-800/70 dark:bg-[#0F0F0F]">
        <div class="flex flex-col gap-1.5 md:flex-row md:items-center md:justify-between">
             <!-- Logo -->
             <a href="{{ route('home') }}" class="flex me-5 ms-2 items-center space-x-3 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:breadcrumbs class="flex flex-wrap gap-1 text-[13px] font-medium text-zinc-800 dark:text-zinc-50">
                    <flux:breadcrumbs.item class="text-[11.5px]" href="{{ route('home') }}">
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

            <div x-data="{ updatedAt: @js($updatedAt) }" @table-list::position-updated.window="updatedAt = '{{ __('agora mesmo') }}'" class="flex flex-wrap items-center gap-3 text-[13px] text-zinc-500 dark:text-zinc-400">
                <flux:separator vertical variant="subtle" class="h-6" />

                <flux:badge size="sm" variant="soft" color="emerald" class="text-[10px] dark:bg-emerald-900/40">
                    {{ __('Updated') }}<span class="ms-1" x-text="updatedAt"></span>
                </flux:badge>
            </div>
        </div>

        <div class="ms-auto flex items-center gap-3 rounded px-3 py-1 bg-gradient-to-r from-blue-50/60 to-emerald-50/70 dark:from-zinc-900/80 dark:to-emerald-900/40 shadow-inner">
            <span class="flex items-center gap-1 text-xs text-zinc-600 dark:text-zinc-200 font-[Space_Mono] tracking-tight select-all">
                <flux:icon.hashtag class="size-3 text-zinc-400 dark:text-emerald-400" />
                {{ $diagram->slug }}
            </span>

            <span class="hidden md:inline px-2 text-xs font-medium text-emerald-700 dark:text-emerald-300 opacity-80">
                {{ __('Level up your diagram! 🚀') }}
            </span>
        </div>

        <div class="ml-auto">
            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                <span class="flex h-full w-full items-center justify-center rounded-full bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                    {{ auth()->user()->initials() }}
                </span>
            </span>
        </div>

    </flux:header>
</div>

