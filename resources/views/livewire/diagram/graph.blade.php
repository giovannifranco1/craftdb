<flux:modal name="diagram-graph" class="w-4xl max-w-4xl">
    <div class="space-y-6 flex flex-col">
        <!-- Header -->
        <div class="space-y-3">
            <flux:heading size="lg">
                {{ __('Graph') }}
            </flux:heading>
        </div>

        <!-- Graph Container -->
        <div class="flex-1 min-h-[600px] flex items-center justify-center">
            <div
                id="diagram-graph-container"
                x-data="diagramGraph(@js($this->getRelationships), @js($this->getTablesWithColors))"
                @diagram-graph::refresh.window="
                    // Update data and re-render graph when data changes
                    relationships = @js($this->getRelationships);
                    tablesColors = @js($this->getTablesWithColors);
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
    </div>
</flux:modal>
