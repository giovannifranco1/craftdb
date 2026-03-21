<section id="how-it-works" class="py-32 relative overflow-hidden bg-black scroll-animate">
    {{-- Background decorations (subtle gradient only, no heavy blur) --}}
    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-gray-900/5 to-transparent pointer-events-none" aria-hidden="true"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-blue-500/5 rounded-full blur-2xl pointer-events-none" aria-hidden="true"></div>

    <div class="container mx-auto px-6 relative">
        {{-- Section Header --}}
        <div class="max-w-3xl mx-auto text-center mb-20">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-sm font-medium mb-6">
                <x-lucide-sparkles class="w-4 h-4" />
                {{ __('landing-page.how_it_works.badge') }}
            </div>
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6 tracking-tight">
                {{ __('landing-page.how_it_works.title_line1') }}
                <br />
                <span class="bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">{{ __('landing-page.how_it_works.title_line2') }}</span>
            </h2>
            <p class="text-xl text-white/60 max-w-2xl mx-auto">
                {{ __('landing-page.how_it_works.subtitle') }}
            </p>
        </div>

        {{-- Steps Grid (pt-8 evita corte do badge pelo overflow da section) --}}
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 pt-8">
            @php
                $steps = __('landing-page.how_it_works.steps');
            @endphp
            @foreach($steps as $index => $step)
                <div class="relative flex">
                    {{-- Connection line (atrás dos cards, só desktop) --}}
                    @if($index < count($steps) - 1)
                        <div class="hidden lg:block absolute top-14 left-1/2 w-1/2 h-px bg-gradient-to-r from-white/20 to-transparent z-0" style="left: calc(50% + 2rem);"></div>
                    @endif

                    <div class="relative z-10 w-full bg-white/[0.07] border border-white/10 rounded-2xl p-8 flex flex-col transition-all duration-300 hover:border-blue-500/30 hover:shadow-xl hover:shadow-blue-500/5 hover:-translate-y-1">
                        {{-- Step number --}}
                        <div class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/10 border border-white/20 flex items-center justify-center text-sm font-bold text-white/70">
                            {{ $index + 1 }}
                        </div>

                        {{-- Icon --}}
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br {{ $step['color'] }} p-0.5 mb-6 shrink-0 overflow-hidden">
                            <div class="w-full h-full rounded-[14px] bg-black flex items-center justify-center">
                                @if($step['icon'] === 'users')
                                    <x-lucide-users class="w-7 h-7 text-white shrink-0" />
                                @elseif($step['icon'] === 'add-table')
                                    <flux:icon.add-table class="w-7 h-7 text-white shrink-0" />
                                @elseif($step['icon'] === 'code')
                                    <x-lucide-file-code class="w-7 h-7 text-white shrink-0" />
                                @elseif($step['icon'] === 'share')
                                    <x-lucide-share-2 class="w-7 h-7 text-white shrink-0" />
                                @endif
                            </div>
                        </div>

                        {{-- Content --}}
                        <h3 class="text-xl font-semibold text-white mb-3 pr-10">
                            {{ $step['title'] }}
                        </h3>
                        <p class="text-white/60 leading-relaxed text-sm">
                            {{ $step['description'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
