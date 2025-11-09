<div x-data="{loading: true}" x-show="loading" @tables-inicializeted.window="loading = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="flex flex-col items-center gap-6">
        <div class="relative w-16 h-16">
            <div class="absolute inset-0 rounded-full border-2 border-gray-300/30 border-t-white animate-spin"></div>
            <div class="absolute inset-2 rounded-full border-2 border-gray-400/20 border-r-white animate-spin" style="animation-duration: 1.2s; animation-direction: reverse;"></div>
        </div>

        <div class="flex flex-col items-center gap-3">
            <span class="text-base font-medium text-gray-200">Carregando tabelas</span>
            <div class="flex gap-1.5">
                <span class="w-1.5 h-1.5 bg-gray-300 rounded-full animate-bounce" style="animation-delay: 0s;"></span>
                <span class="w-1.5 h-1.5 bg-gray-300 rounded-full animate-bounce" style="animation-delay: 0.2s;"></span>
                <span class="w-1.5 h-1.5 bg-gray-300 rounded-full animate-bounce" style="animation-delay: 0.4s;"></span>
            </div>
        </div>

        <div class="w-48 h-0.5 bg-gray-700/50 rounded-full overflow-hidden">
            <div class="h-full bg-gray-300 rounded-full animate-[progress_2s_ease-in-out_infinite]"></div>
        </div>
    </div>

    <style>
        @keyframes progress {
            0% { width: 0%; margin-left: 0%; }
            50% { width: 70%; margin-left: 15%; }
            100% { width: 100%; margin-left: 0%; }
        }
    </style>
</div>
