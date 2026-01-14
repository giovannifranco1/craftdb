<section class="py-16 border-y border-gray-200/50">
    <div class="container mx-auto px-6">
        <p class="text-center text-sm text-gray-600 mb-8">
            Usado por times de engenharia em empresas como
        </p>
        <div class="flex flex-wrap items-center justify-center gap-8 md:gap-12">
            @php
                $logos = [
                    ['name' => 'TechFlow', 'initials' => 'TF'],
                    ['name' => 'DataSync', 'initials' => 'DS'],
                    ['name' => 'CloudBase', 'initials' => 'CB'],
                    ['name' => 'DevStack', 'initials' => 'DV'],
                    ['name' => 'AppForge', 'initials' => 'AF'],
                    ['name' => 'CodeLab', 'initials' => 'CL'],
                ];
            @endphp

            @foreach($logos as $logo)
                <div class="flex items-center gap-2 opacity-40 hover:opacity-70 transition-opacity duration-300 grayscale">
                    <div class="w-8 h-8 rounded-lg bg-gray-600/20 flex items-center justify-center">
                        <span class="text-xs font-bold text-gray-600">
                            {{ $logo['initials'] }}
                        </span>
                    </div>
                    <span class="text-lg font-semibold text-gray-600">
                        {{ $logo['name'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</section>
