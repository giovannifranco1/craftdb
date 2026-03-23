<x-layouts.auth>

    <div class="flex flex-col gap-6" x-init="() => $flux.dark = false">
        <x-auth-header :title="__('Welcome back')" :description="__('Sign in to continue')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email')"
                type="email"
                required
                autofocus
                value="{{ old('email') }}"
                autocomplete="email"
                placeholder="you@example.com"
            />

            <!-- Password -->
            <div class="flex flex-col gap-2">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <div class="flex justify-end">
                        <flux:link class="text-xs text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300" :href="route('password.request')" wire:navigate>
                            {{ __('Forgot password?') }}
                        </flux:link>
                    </div>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

            <!-- Accept Terms and Policy -->
            <flux:field>
                <div class="flex items-start gap-3">
                    <flux:checkbox
                        name="accept_terms"
                        value="1"
                        :checked="(bool) old('accept_terms')"
                        :aria-label="__('auth.accept_terms_aria')"
                        class="mt-1 shrink-0"
                    />
                    <flux:text class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                        {{ __('auth.login_terms_prefix') }}
                        <flux:link :href="route('landing.terms')" target="_blank" rel="noopener" class="font-medium underline">
                            {{ __('auth.terms_of_service') }}
                        </flux:link>
                        {{ __('auth.login_terms_between') }}
                        <flux:link :href="route('landing.policy')" target="_blank" rel="noopener" class="font-medium underline">
                            {{ __('auth.privacy_policy') }}
                        </flux:link>.
                    </flux:text>
                </div>
                <flux:error name="accept_terms" />
            </flux:field>

            <!-- Submit Button -->
            <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                {{ __('Sign in') }}
            </flux:button>
        </form>

        <!-- Divider -->
        <div class="relative flex items-center gap-4">
            <div class="flex-1 border-t border-zinc-200 dark:border-zinc-800"></div>
            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Or continue with') }}</span>
            <div class="flex-1 border-t border-zinc-200 dark:border-zinc-800"></div>
        </div>

        <!-- Social Login Buttons -->
        <div class="flex flex-col gap-3">
            <a
                class="flex w-full items-center justify-center gap-3 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950/50 px-4 py-3 text-sm font-medium text-zinc-700 dark:text-zinc-300 shadow-sm transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-900/50"
                href="{{ route('auth.google.redirect') }}"
            >
                <flux:icon.google class="size-5" />
                <span>{{ __('Continue with Google') }}</span>
            </a>

            <a
                class="flex w-full items-center justify-center gap-3 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950/50 px-4 py-3 text-sm font-medium text-zinc-700 dark:text-zinc-300 shadow-sm transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-900/50"
                href="{{ route('auth.github.redirect') }}"
            >
                <flux:icon.github class="size-5" />
                <span>{{ __('Continue with GitHub') }}</span>
            </a>
        </div>

        @if (Route::has('register'))
            <div class="text-center">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    {{ __('Don\'t have an account?') }}
                    <flux:link :href="route('register')" wire:navigate class="font-medium text-zinc-900 dark:text-zinc-100 hover:underline">
                        {{ __('Sign up') }}
                    </flux:link>
                </p>
            </div>
        @endif
    </div>
</x-layouts.auth>
