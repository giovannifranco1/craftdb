<header class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-xl border-b border-gray-200/30">
    <div class="container mx-auto px-6">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex me-5 ms-5 items-center rtl:space-x-reverse">
                <div class="flex items-center size-15 justify-center rounded-md text-accent-foreground">
                    <div>
                        <img src="{{ Vite::asset('resources/images/final_flat_logo.svg') }}" alt="CraftDB Logo" {{ $attributes }} class="size-15">
                    </div>
                </div>

                <div class="flex flex-col leading-none">
                    <span class="text-3xl uppercase font-[Black_Ops_One] lh-normal text-transparent bg-gradient-to-r from-blue-700 via-blue-500 to-zinc-900 bg-clip-text z-10">CraftDB</span>
                    <span class="text-[0.800rem] ms-auto -mt-1 font-[Black_Ops_One]">Beta</span>
                </div>

            </a>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center gap-8">
                <a href="#features" class="nav-link text-sm">Recursos</a>
                <a href="#how-it-works" class="nav-link text-sm">Como funciona</a>
                <a href="#pricing" class="nav-link text-sm">Preços</a>
                <a href="#faq" class="nav-link text-sm">FAQ</a>
            </nav>

            <!-- Desktop CTA -->
            <div class="hidden md:flex items-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 landing-link-hover transition">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-gray-600 landing-link-hover transition">
                        Entrar
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-2 landing-primary-bg text-white text-sm rounded-lg transition">
                        Começar grátis
                    </a>
                @endauth
            </div>

            <!-- Mobile Menu Button -->
            <button
                @click="mobileMenuOpen = !mobileMenuOpen"
                class="md:hidden p-2 text-gray-900"
            >
                <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div
            x-show="mobileMenuOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="md:hidden py-4 border-t border-gray-200/30"
        >
            <nav class="flex flex-col gap-4">
                <a href="#features" @click="mobileMenuOpen = false" class="nav-link text-sm py-2">Recursos</a>
                <a href="#how-it-works" @click="mobileMenuOpen = false" class="nav-link text-sm py-2">Como funciona</a>
                <a href="#pricing" @click="mobileMenuOpen = false" class="nav-link text-sm py-2">Preços</a>
                <a href="#faq" @click="mobileMenuOpen = false" class="nav-link text-sm py-2">FAQ</a>

                <div class="flex flex-col gap-2 pt-4 border-t border-gray-200/30">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 landing-link-hover transition text-center py-2">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-600 landing-link-hover transition text-center py-2">
                            Entrar
                        </a>
                        <a href="{{ route('register') }}" class="px-4 py-2 landing-primary-bg text-white text-sm rounded-lg transition text-center">
                            Começar grátis
                        </a>
                    @endauth
                </div>
            </nav>
        </div>
    </div>
</header>
