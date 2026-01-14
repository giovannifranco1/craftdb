<section class="relative min-h-screen flex items-center justify-center pt-32 overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute inset-0 hero-gradient opacity-50"></div>
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl"></div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="max-w-4xl mx-auto text-center">
            <!-- Award Badge -->
            <div class="inline-flex items-center gap-3 px-4 py-2 rounded-full bg-amber-500/10 border border-amber-500/30 mb-6 fade-in">
                <span class="text-lg">🏆</span>
                <span class="text-sm font-medium text-amber-600">#1 Ferramenta de Database Design 2024</span>
            </div>

            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-600/10 border border-blue-600/20 mb-8 fade-in">
                <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                <span class="text-sm text-blue-600">Novo: Suporte a múltiplos temas e colaboração em tempo real</span>
            </div>

            <!-- Headline -->
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold font-[Inter] text-gray-900 mb-6 leading-tight fade-in-up">
                A forma mais visual de <span class="bg-gradient-to-r from-blue-600 via-cyan-500 to-blue-600 bg-clip-text text-transparent">projetar seu banco de dados</span>
            </h1>

            <!-- Subheadline -->
            <p class="text-lg md:text-xl text-gray-600 mb-10 max-w-2xl mx-auto fade-in-up fade-in-delay-1">
                Crie diagramas de banco de dados intuitivos. Defina tabelas, relacionamentos
                e colabore em tempo real. Zero complexidade, máxima produtividade.
            </p>

            <!-- CTAs -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-16 fade-in-up fade-in-delay-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-6 py-3 landing-btn-primary font-bold text-white text-sm rounded-lg transition shadow-sm">
                        Ir para Dashboard
                        <x-lucide-arrow-right class="w-4 h-4 ml-2" />
                    </a>
                @else
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-6 py-3 landing-btn-primary font-bold text-white text-sm rounded-lg transition shadow-sm">
                        Começar grátis
                        <x-lucide-arrow-right class="size-4 ml-2" />
                    </a>
                @endauth
                <a href="#demo" class="inline-flex items-center bg-white justify-center px-6 py-3 border-1 border-gray-300/50 rounded-lg font-medium text-sm landing-btn-outline transition">
                    <x-lucide-play class="size-4 mr-2" />
                    Ver demonstração
                </a>
            </div>

            <!-- Visual - Laptop Mockup with Video -->
            <div
                class="relative max-w-4xl mx-auto fade-in-up fade-in-delay-3"
                x-data="{
                    isPlaying: false,
                    previewPlaying: false,
                    init() {
                        const observer = new IntersectionObserver((entries) => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting && !this.isPlaying) {
                                    this.previewPlaying = true;
                                }
                            });
                        }, { threshold: 0.5 });

                        observer.observe(this.$el);
                    }
                }"
            >
                <!-- Laptop Frame -->
                <div class="relative">
                    <!-- Screen bezel -->
                    <div class="bg-gray-900/90 rounded-t-2xl p-3 pb-0">
                        <!-- Camera notch -->
                        <div class="w-3 h-3 rounded-full bg-gray-600/30 mx-auto mb-2"></div>

                        <!-- Screen content -->
                        <div class="bg-gray-200 rounded-t-lg overflow-hidden aspect-video relative">
                            <!-- Full video when clicked -->
                            <template x-if="isPlaying">
                                <iframe
                                    src="https://www.youtube.com/embed/dQw4w9WgXcQ?autoplay=1&mute=0&controls=1&rel=0"
                                    title="CraftDB Demo"
                                    class="absolute inset-0 w-full h-full"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                ></iframe>
                            </template>

                            <!-- Preview video with play button overlay -->
                            <template x-if="!isPlaying">
                                <div class="absolute inset-0">
                                    <!-- Video preview (muted autoplay) -->
                                    <iframe
                                        x-show="previewPlaying"
                                        src="https://www.youtube.com/embed/dQw4w9WgXcQ?autoplay=1&mute=1&controls=0&loop=1&playlist=dQw4w9WgXcQ&rel=0&modestbranding=1"
                                        title="CraftDB Demo Preview"
                                        class="absolute inset-0 w-full h-full pointer-events-none"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    ></iframe>

                                    <!-- Gradient overlay when not playing preview -->
                                    <div
                                        x-show="!previewPlaying"
                                        class="absolute inset-0 bg-gradient-to-br from-blue-600/10 via-white to-cyan-500/10"
                                    ></div>

                                    <!-- Play button overlay (always visible) -->
                                    <div
                                        @click="isPlaying = true; previewPlaying = false"
                                        class="absolute inset-0 flex items-center justify-center cursor-pointer group"
                                    >
                                        <button class="cursor-pointer inline-flex items-center gap-3 px-4 py-2 landing-btn-primary text-white font-medium rounded-3xl shadow-lg transition-all duration-300 group-hover:scale-105 group-hover:shadow-xl">
                                            <div class="size-7 rounded-full bg-white flex items-center justify-center border-1 shadow-[inset_0_0_0_1px_rgba(0,0,0,0.90)]">
                                                <x-lucide-play class="size-3 fill-current text-blue-600 ml-0.5" />
                                             </div>
                                            <span class="font-medium font-[Inter] text-sm">{{ __('Watch demo') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Laptop base/keyboard -->
                    <div class="bg-gradient-to-b from-gray-600/20 to-gray-600/30 h-4 rounded-b-lg"></div>

                    <!-- Laptop bottom edge -->
                    <div class="bg-gray-900/80 h-2 w-[60%] mx-auto rounded-b-xl"></div>
                </div>

                <!-- Reflection/Shadow effect -->
                <div class="absolute -bottom-8 left-1/2 -translate-x-1/2 w-[80%] h-8 bg-gray-900/5 blur-2xl rounded-full"></div>
            </div>
        </div>
    </div>
</section>
