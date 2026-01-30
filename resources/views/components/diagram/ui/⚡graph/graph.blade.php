<flux:modal name="diagram-graph" class="w-4xl max-w-4xl">

    <div class="space-y-6 flex flex-col">
        <!-- Header -->
        <div class="space-y-3">
            <flux:heading size="lg">
                {{ __('Graph') }}
            </flux:heading>
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
