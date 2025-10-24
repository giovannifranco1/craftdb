<a href="{{ route('diagram.show', [$diagram->team_id, $diagram->slug]) }}" class="block group">
    <div class="w-80 h-64 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-2xl hover:border-blue-400 dark:hover:border-blue-500 transition-all duration-300 overflow-hidden relative">
        <!-- Gradient overlay no hover -->
        <div class="absolute inset-0 bg-linear-to-br from-blue-50/0 to-blue-100/0 group-hover:from-blue-50/50 group-hover:to-blue-100/30 dark:from-blue-900/0 dark:to-blue-800/0 dark:group-hover:from-blue-900/20 dark:group-hover:to-blue-800/10 transition-all duration-300"></div>

        <div class="relative z-10 p-6 h-full flex flex-col">
            <!-- Header com ícone e título -->
            <div class="flex items-start space-x-4 mb-4">
                <div class="bg-linear-to-br from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 p-3 rounded-xl shadow-lg">
                    <flux:icon.table-cells class="text-white w-6 h-6" />
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300 truncate">
                        {{ $diagram->name }}
                    </h2>
                    <div class="w-8 h-1 bg-linear-to-r from-blue-500 to-blue-600 rounded-full mt-1 group-hover:w-12 transition-all duration-300"></div>
                </div>
            </div>

            <!-- Descrição -->
            <div class="flex-1 mb-4">
                @if (!is_null($diagram->description))
                    <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed line-clamp-3">{{ $diagram->description }}</p>
                @else
                    <p class="text-gray-400 dark:text-gray-500 text-sm italic">Nenhuma descrição disponível</p>
                @endif
            </div>

            <!-- Footer com data e botão -->
            <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700">
                <div class="flex flex-col">
                    <small class="text-gray-500 dark:text-gray-400 text-xs font-medium">
                        {{ $diagram->created_at->format('d/m/Y') }}
                    </small>
                    <small class="text-gray-400 dark:text-gray-500 text-xs">
                        {{ $diagram->created_at->format('h:i') }}
                    </small>
                </div>

                <div class="bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-700 dark:to-blue-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-lg group-hover:from-blue-700 group-hover:to-blue-800 dark:group-hover:from-blue-600 dark:group-hover:to-blue-700 group-hover:shadow-xl transition-all duration-300 transform group-hover:scale-105">
                    Abrir →
                </div>
            </div>
        </div>
    </div>
</a>
