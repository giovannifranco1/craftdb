<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        @include('_partials.head')
    </head>

    @use('App\Models\Team\Team')
    @php
        $team = Team::currentTeam();
    @endphp
    <body class="min-h-screen bg-zinc-50 dark:bg-background-dark">

        @persist('toast')
        <x-ui.toast/>
        @endpersist

        <!-- Horizontal Navigation Header -->
        <flux:header sticky class="border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-background-dark z-40">
            <div class="flex w-full">
                <!-- Top bar -->
                <div class="flex items-center w-full my-1">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <a href="{{ route('dashboard') }}" class="flex me-5 items-center rtl:space-x-reverse" wire:navigate>
                            <x-app-logo />
                        </a>

                        @if(request()->routeIs('diagram.show'))
                            <flux:breadcrumbs class="ms-5">
                                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Diagrams</flux:breadcrumbs.item>
                                <flux:breadcrumbs.item href="{{ route('diagram.show', [$team->id, $diagram->slug])}}">{{ $diagram->name }}</flux:breadcrumbs.item>
                            </flux:breadcrumbs>
                        @endif
                    </div>

                    <!-- Navbar -->
                    <flux:navbar class="-mb-px max-lg:hidden">
                        @php
                            $planBadge = auth()->user()->isTeamPlan() ? 'Team' : (auth()->user()->isPro() ? 'Pro' : 'Free');
                            $planColor = auth()->user()->isTeamPlan() ? 'violet' : (auth()->user()->isPro() ? 'lime' : 'sky');
                        @endphp
                        <flux:navbar.item badge="{{ $planBadge }}" badge:color="{{ $planColor }}" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Diagrams') }}</flux:navbar.item>
                    </flux:navbar>

                    <div class="flex items-center space-x-4 ml-auto">
                        <flux:button class="cursor-pointer" @click="$flux.modal('diagram-create').show()" icon="plus" variant="primary" color="sky" size="sm">
                            {{ __('New Diagram') }}
                        </flux:button>

                         <!-- User Menu -->
                        <flux:dropdown position="bottom" align="end">
                            <flux:profile
                                :name="auth()->user()->name"
                                :initials="auth()->user()->initials()"
                                icon:trailing="chevron-down"
                                data-test="sidebar-menu-button"
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

                                @can('create', \App\Models\Team\Team::class)
                                    <flux:menu.separator />
                                    <flux:menu.item as="button" icon="user-plus" type="button" :href="route('team.create')">{{__('Create New Team')}}</flux:menu.item>
                                @endcan

                                <flux:menu.separator/>

                                <livewire:ui.team-switcher/>

                                <flux:menu.radio.group>
                                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                                    <flux:menu.item :href="route('billing.index')" icon="credit-card" wire:navigate>{{ __('Billing') }}</flux:menu.item>
                                    <flux:menu.item class="cursor-pointer" as="button" type="button" icon="chat-bubble-left-ellipsis" @click="$flux.modal('contact-us').show()">
                                        {{ __('Contact Us') }}
                                    </flux:menu.item>
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
            </div>
        </flux:header>

        {{ $slot }}

        <x-flux::footer style="margin: 0px; padding: 0px;">
            @include('_partials.footer')
        </x-flux::footer>

        <livewire:diagram.create/>
        <livewire:ui.contact-us/>
        <x-ui.pro-feature-modal/>
        @fluxScripts
    </body>
</html>
