<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        @include('partials.head')
    </head>

    @use('App\Models\Team\Team')

    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <!-- Horizontal Navigation Header -->
        <flux:header sticky class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 z-50">
            <div class="flex flex-col w-full">
                <!-- Top bar -->
                <div class="flex items-center justify-between w-full my-3">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <a href="{{ route('home') }}" class="flex me-5 ms-2 items-center space-x-3 rtl:space-x-reverse" wire:navigate>
                            <x-app-logo />
                        </a>

                        @if(request()->routeIs('diagram.show'))
                            @php
                                $team = Team::currentTeam();
                            @endphp

                            <flux:breadcrumbs class="ms-5">
                                <flux:breadcrumbs.item href="{{ route('home') }}">Diagrams</flux:breadcrumbs.item>
                                <flux:breadcrumbs.item href="{{ route('diagram.show', [$team->id, $diagram->slug])}}">{{ $diagram->name }}</flux:breadcrumbs.item>
                            </flux:breadcrumbs>
                        @endif
                    </div>

                    <div class="flex items-center space-x-4">
                        <flux:button href="#" icon="plus" variant="primary" color="blue" size="sm">
                            Novo Diagrama
                        </flux:button>

                         <!-- User Menu -->
                        <flux:dropdown position="bottom" align="end">
                            <flux:profile
                                :name="auth()->user()->name"
                                :initials="auth()->user()->initials()"
                                icon:trailing="chevron-down"
                                data-test="sidebar-menu-button"
                            />

                            <flux:menu class="w-[220px]">
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

                                <flux:menu.separator />

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
                </div>

                <!-- Navbar -->
                <flux:navbar class="-mb-px max-lg:hidden mt-2">
                    <flux:navbar.item badge="Pro" badge:color="lime" href="#" :href="route('home')" :current="request()->routeIs('home')" wire:navigate>{{ __('Diagrams') }}</flux:navbar.item>
                    <flux:separator vertical variant="subtle" class="my-2"/>
                </flux:navbar>
            </div>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
