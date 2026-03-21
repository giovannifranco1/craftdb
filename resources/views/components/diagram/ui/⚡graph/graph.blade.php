<div>
    <flux:modal name="diagram-graph" class="w-4xl max-w-4xl">

        <div class="space-y-6 flex flex-col">
            <!-- Header -->
            <div class="flex items-center gap-2">
                <flux:heading size="lg">
                    {{ __('Graph') }}
                </flux:heading>
                <flux:button
                    icon="information-circle"
                    variant="ghost"
                    size="sm"
                    x-on:click="$flux.modal('graph-info').show()"
                    class="cursor-pointer text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300"
                />
            </div>
            @if($this->loadGraph)
                <!-- Graph Container -->
                <div class="flex-1 min-h-[600px] flex items-center justify-center">
                        <div id="diagram-graph-container"
                            x-data="diagramGraph(@js($this->relationships), @js($this->tablesWithColors))"
                            @diagram-graph::refresh.window="
                                relationships = @js($this->relationships);
                                tablesColors = @js($this->tablesWithColors);
                                svg = null;
                                if (setupGraphWrapper) {
                                    setupGraphWrapper();
                                } else {
                                    setTimeout(() => {
                                        if ($el && !svg) {
                                            setupGraph();
                                        }
                                    }, 100);
                                }
                            "
                            class="w-full h-full flex items-center justify-center"
                            style="min-height: 600px;"
                        ></div>
                </div>
            @endif
        </div>

    </flux:modal>

    <flux:modal name="graph-info" class="md:w-[40rem]">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('How the Graph works') }}</flux:heading>

            <flux:text>
                {{ __('The Graph shows all tables in the diagram as nodes, connected by arrows that represent foreign key dependencies.') }}
            </flux:text>

            <div class="space-y-3">
                <flux:subheading>{{ __('Dependency rules') }}</flux:subheading>

                <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                    <li class="flex items-start gap-2">
                        <span class="mt-0.5 font-mono text-xs bg-zinc-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded">1:n / n:1</span>
                        <span>{{ __('Always shown — the child table depends on the parent.') }}</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-0.5 font-mono text-xs bg-zinc-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded">n:n</span>
                        <span>{{ __('Always shown — both sides depend on each other through a join table.') }}</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-0.5 font-mono text-xs bg-zinc-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded">1:1</span>
                        <span>{{ __('Only shown when the FK column is NOT NULL — meaning the dependency is required.') }}</span>
                    </li>
                </ul>
            </div>

            <flux:text>
                {{ __('Arrows point from the dependent table (the one holding the FK) toward the referenced table. You can drag nodes and zoom the graph freely.') }}
            </flux:text>

            <div class="flex justify-end pt-2">
                <flux:button variant="ghost" x-on:click="$flux.modal('graph-info').close()" class="cursor-pointer">
                    {{ __('Got it') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
