@php
    // Por enquanto usando valor padrão, mas pode ser substituído por $diagram->database quando o campo for adicionado
    $database = 'mysql'; // TODO: Adicionar campo database na tabela diagram
    $databaseLabels = [
        'mysql' => 'MySQL',
        'postgresql' => 'PostgreSQL',
        'sqlite' => 'SQLite',
    ];
    $databaseColors = [
        'mysql' => 'blue',
        'postgresql' => 'indigo',
        'sqlite' => 'emerald',
    ];

    // Style do diagrama
    $style = $diagram->style ?? 'neutral';
    $styleLabels = [
        'colorful' => 'Colorful',
        'neutral' => 'Neutral',
    ];
    $styleColors = [
        'colorful' => 'red',
        'neutral' => 'zinc',
    ];

    // Estatísticas do diagrama
    $tablesCount = $diagram->tables_count ?? $diagram->tables()->count();
    $relationshipsCount = $diagram->relationships()->count();
@endphp

<a href="{{ route('diagram.show', [$diagram->team_id, $diagram->slug]) }}" class="block group" {{ $attributes }}>
    <div class="w-80 h-64 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
        <div class="p-6 h-full flex flex-col">
            <!-- Header com ícone, título e badges -->
            <div class="flex items-start gap-4 mb-4">
                <div class="flex-shrink-0 p-3 rounded-lg bg-primary-800 dark:bg-primary-800">
                    <flux:icon.graph class="size-6 text-white" />
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors duration-300 truncate mb-2">
                        {{ $diagram->name }}
                    </h3>
                    <div class="flex items-center gap-2 flex-wrap">
                        <flux:badge
                            size="sm"
                            variant="soft"
                            color="{{ $databaseColors[$database] ?? 'blue' }}"
                            class="shrink-0"
                        >
                            <flux:icon name="database.{{ $database }}" class="size-3 me-1" />
                            {{ $databaseLabels[$database] ?? ucfirst($database) }}
                        </flux:badge>
                        <flux:badge
                            size="sm"
                            variant="soft"
                            color="{{ $styleColors[$style] ?? 'zinc' }}"
                            class="shrink-0"
                        >
                            <flux:icon.swatch class="size-3 me-1" />
                            {{ $styleLabels[$style] ?? ucfirst($style) }}
                        </flux:badge>
                    </div>
                </div>
            </div>

            <!-- Descrição -->
            <div class="flex-1 mb-4 min-h-0">
                @if (!is_null($diagram->description))
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed line-clamp-2 mb-3">
                        {{ $diagram->description }}
                    </p>
                @else
                    <p class="text-sm text-zinc-400 dark:text-zinc-500 italic mb-3">
                        Nenhuma descrição disponível
                    </p>
                @endif

                <!-- Estatísticas -->
                <div class="flex items-center gap-4 text-xs text-zinc-500 dark:text-zinc-400">
                    <div class="flex items-center gap-1.5">
                        <flux:icon.table class="size-4" />
                        <span class="font-medium">{{ $tablesCount }}</span>
                        <span>{{ $tablesCount === 1 ? 'tabela' : 'tabelas' }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <flux:icon.link class="size-4" />
                        <span class="font-medium">{{ $relationshipsCount }}</span>
                        <span>{{ $relationshipsCount === 1 ? 'relacionamento' : 'relacionamentos' }}</span>
                    </div>
                </div>
            </div>

            <!-- Footer com data e botão -->
            <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-1.5">
                        <flux:icon.calendar class="size-3 text-zinc-400 dark:text-zinc-500" />
                        <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400 font-medium">
                            {{ $diagram->created_at->format('d/m/Y') }}
                        </flux:text>
                    </div>
                    @if($diagram->updated_at && $diagram->updated_at->ne($diagram->created_at))
                        <div class="flex items-center gap-1.5">
                            <flux:icon.clock class="size-3 text-zinc-400 dark:text-zinc-500" />
                            <flux:text class="text-zinc-400 dark:text-zinc-500 text-xs">
                                {{ __('Updated') }} <br> {{ $diagram->updated_at->diffForHumans() }}
                            </flux:text>
                        </div>
                    @endif
                </div>

                <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary-800 dark:bg-primary-500 text-white text-sm font-medium shadow-sm group-hover:bg-primary-700 dark:group-hover:bg-primary-800 transition-all duration-300">
                    Ver Diagrama
                    <flux:icon.arrow-right class="size-4" />
                </span>
            </div>
        </div>
    </div>
</a>
