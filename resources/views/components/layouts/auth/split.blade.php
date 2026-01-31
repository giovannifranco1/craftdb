<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        @include('_partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="bg-muted relative hidden h-full flex-col p-10 text-white lg:flex dark:border-e dark:border-neutral-800">
                <div class="absolute inset-0 bg-background-dark bg-cover bg-center bg-no-repeat" style="background-image: url('{{ Vite::asset('resources/images/auth/molecular_login_bg_final.png') }}');"></div>
                <a href="{{ route('dashboard') }}" class="relative z-20 flex items-center text-lg font-medium" wire:navigate>
                    <span class="flex size-10 me-2 items-center justify-center rounded-md">
                        <x-app-logo-icon class="size-full fill-current text-white" />
                    </span>
                    <span class="ml-2 flex flex-col leading-none">
                        <span class="text-3xl font-semibold font-[Outfit] text-white mb-1">CraftDB</span>
                        <span class="text-sm font-medium -mt-2.5 ml-0.5 font-[Outfit] text-left self-start">Beta</span>
                    </span>
                </a>

                @php
                    [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                @endphp

                <div class="relative z-20 mt-auto">
                    <blockquote class="space-y-2">
                        <flux:heading size="lg" class="text-primary-100">&ldquo;{{ trim($message) }}&rdquo;</flux:heading>
                        <footer><flux:heading class="text-primary-100">{{ trim($author) }}</flux:heading></footer>
                    </blockquote>
                </div>
            </div>
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('dashboard') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden" wire:navigate>
                        <span class="flex h-20 w-20 items-center justify-center rounded-md">
                            <x-app-logo-icon class="size-20 fill-current text-black dark:text-white" />
                        </span>

                        <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
