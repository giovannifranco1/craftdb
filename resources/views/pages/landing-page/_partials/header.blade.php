<header
    class="fixed top-0 left-0 right-0 z-50 backdrop-blur-sm transition-all duration-300"
    x-data="{ scrolled: false }"
    x-init="window.addEventListener('scroll', () => { scrolled = window.pageYOffset > 50 })"
    :class="scrolled ? 'bg-white/95 shadow-sm' : 'bg-transparent'"
>
    <div class="container mx-auto px-6">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <div class="w-20 h-20 flex items-center justify-center">
                    <x-app-logo-icon class="size-20 fill-current" />
                </div>
                <span class="text-3xl font-semibold font-[Outfit] text-gray-900">CraftDB</span>
            </a>

            <!-- Desktop Navigation + CTA -->
            <div class="hidden md:flex items-center gap-10">
                <nav class="flex items-center gap-8">
                    <a href="#features" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                        Recursos
                    </a>
                    <a href="#pricing" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                        Preços
                    </a>
                    <a href="#how-it-works" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                        Como funciona
                    </a>
                    <a href="#faq" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                        FAQ
                    </a>
                </nav>
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2 text-sm font-medium text-white bg-gray-900 rounded-full hover:bg-gray-800 transition-colors">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-5 py-2 text-sm font-medium text-white bg-gray-900 rounded-full hover:bg-gray-800 transition-colors">
                        Entrar
                    </a>
                @endauth
            </div>

            <!-- Mobile Menu Button -->
            <button
                @click="mobileMenuOpen = !mobileMenuOpen"
                class="md:hidden p-2 text-gray-900"
            >
                <x-lucide-menu x-show="!mobileMenuOpen" class="w-6 h-6" />
                <x-lucide-x x-show="mobileMenuOpen" class="w-6 h-6" />
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
            class="md:hidden py-4 border-t border-gray-200"
        >
            <nav class="flex flex-col gap-4">
                <a href="#features" @click="mobileMenuOpen = false" class="text-sm font-medium text-gray-600 hover:text-gray-900 py-2">
                    Recursos
                </a>
                <a href="#pricing" @click="mobileMenuOpen = false" class="text-sm font-medium text-gray-600 hover:text-gray-900 py-2">
                    Preços
                </a>
                <a href="#how-it-works" @click="mobileMenuOpen = false" class="text-sm font-medium text-gray-600 hover:text-gray-900 py-2">
                    Como funciona
                </a>
                <a href="#faq" @click="mobileMenuOpen = false" class="text-sm font-medium text-gray-600 hover:text-gray-900 py-2">
                    FAQ
                </a>

                <div class="pt-4 border-t border-gray-200">
                    @auth
                        <a href="{{ route('dashboard') }}" class="block w-full px-5 py-2 text-sm font-medium text-white bg-gray-900 rounded-full text-center hover:bg-gray-800 transition-colors">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="block w-full px-5 py-2 text-sm font-medium text-white bg-gray-900 rounded-full text-center hover:bg-gray-800 transition-colors">
                            Entrar
                        </a>
                    @endauth
                </div>
            </nav>
        </div>
    </div>
</header>
