<section class="py-20 bg-gray-100/30">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                Suporte a diversos
                <span class="gradient-text">bancos de dados</span>
            </h2>
            <p class="text-center text-gray-600 max-w-2xl mx-auto">
                Compatível com os principais bancos relacionais e tecnologias
            </p>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-6 md:gap-10">
            {{-- MySQL --}}
            <div class="w-full md:w-auto justify-center flex items-center gap-2.5 px-5 py-3 rounded-xl bg-white border border-gray-200/50 shadow-xs hover:shadow-md hover:border-gray-200 transition-all duration-300">
                <flux:icon.database.mysql class="size-8 shrink-0" />
                <span class="text-sm font-semibold text-gray-900">MySQL</span>
            </div>
            {{-- PostgreSQL --}}
            <div class="w-full md:w-auto justify-center flex items-center gap-2.5 px-5 py-3 rounded-xl bg-white border border-gray-200/50 shadow-xs hover:shadow-md hover:border-gray-200 transition-all duration-300">
                <flux:icon.database.postgresql class="size-8 shrink-0" />
                <span class="text-sm font-semibold text-gray-900">PostgreSQL</span>
            </div>
            {{-- SQLite --}}
            <div class="w-full md:w-auto justify-center flex items-center gap-2.5 px-5 py-3 rounded-xl bg-white border border-gray-200/50 shadow-xs hover:shadow-md hover:border-gray-200 transition-all duration-300">
                <flux:icon.database.sqlite class="size-8 shrink-0" />
                <span class="text-sm font-semibold text-gray-900">SQLite</span>
            </div>
            {{-- Laravel Migration --}}
            <div class="w-full md:w-auto justify-center flex items-center gap-2.5 px-5 py-3 rounded-xl bg-white border border-gray-200/50 shadow-xs hover:shadow-md hover:border-gray-200 transition-all duration-300">
                <flux:icon.framework.laravel class="size-8 shrink-0 text-[#FF2D20]" />
                <span class="text-sm font-semibold text-gray-900">Laravel Migration</span>
            </div>
        </div>
    </div>
</section>
