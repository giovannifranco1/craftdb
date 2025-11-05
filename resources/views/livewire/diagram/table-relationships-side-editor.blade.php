<section
    x-data="{
        open: @entangle('open'),
        handleEscape(e) {
            if (e.key === 'Escape' && this.open) {
                e.stopImmediatePropagation();
                e.preventDefault();
                this.open = false;
            }
        }
    }"
    x-init="
        const handler = (e) => $data.handleEscape(e);
        window.addEventListener('keydown', handler, true);
        $el.addEventListener('alpine:destroy', () => {
            window.removeEventListener('keydown', handler, true);
        });
    "
    x-show="open"
    x-cloak
    class="relative z-10"
    aria-labelledby="slide-over-title"
    x-ref="dialog"
    aria-modal="true"
>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="ease-in-out duration-250"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in-out duration-250"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-200/50 transition-opacity"
    ></div>

    <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">

                <div
                    x-show="open"
                    x-cloak
                    x-transition:enter="transform transition ease-in-out duration-250 sm:duration-500"
                    x-transition:enter-start="translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-250 sm:duration-500"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="translate-x-full"
                    class="pointer-events-auto w-screen max-w-[650px] shadow-2xs"
                >
                    <div class="flex h-full flex-col overflow-y-scroll bg-white py-6 shadow-xl">
                        <header class="px-4 sm:px-6">
                            <div class="flex items-start justify-between">
                                <h2 class="text-lg font-medium text-gray-900" id="slide-over-title">
                                    {{ ucfirst($title) }}
                                </h2>
                                <div class="ml-3 flex h-7 items-center">
                                    <button
                                        type="button"
                                        class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        @click.stop="open = false"
                                    >
                                        <span class="sr-only">Close panel</span>
                                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </header>
                        <article class="relative mt-4 flex-1 px-4 sm:px-6">
                            <div class="space-y-2 overflow-y-auto max-h-[calc(100vh-180px)]">
                                @forelse($this->getRelationships() as $relationship)
                                    @php
                                        $isFromTable = $relationship->from_table_id === $table->id;
                                        $sourceTable = $isFromTable ? $table : $relationship->fromTable;
                                        $targetTable = $isFromTable ? $relationship->toTable : $table;
                                        $sourceColumn = $isFromTable ? $relationship->fromColumn : $relationship->toColumn;
                                        $targetColumn = $isFromTable ? $relationship->toColumn : $relationship->fromColumn;
                                    @endphp

                                    <div class="group bg-white rounded border border-gray-200 hover:border-indigo-300 hover:shadow-sm transition-all duration-150 p-2.5">
                                        <!-- Header compacto -->
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-1.5">
                                                @if($relationship->type === '1:1')
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-800">1:1</span>
                                                @elseif($relationship->type === '1:n')
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-green-100 text-green-800">1:N</span>
                                                @else
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-purple-100 text-purple-800">N:N</span>
                                                @endif

                                                @if($relationship->label)
                                                    <span class="text-xs text-gray-600 italic">{{ $relationship->label }}</span>
                                                @endif
                                            </div>

                                            @if($isFromTable)
                                                <span class="text-[10px] text-indigo-600 font-medium">→ Outgoing</span>
                                            @else
                                                <span class="text-[10px] text-green-600 font-medium">← Incoming</span>
                                            @endif
                                        </div>

                                        <!-- Flow horizontal compacto -->
                                        <div class="flex items-center gap-2">
                                            <!-- Source -->
                                            <div class="flex-1 min-w-0 bg-gray-50 rounded border border-gray-200 p-1.5">
                                                <div class="flex items-center gap-1.5 mb-1">
                                                    <div class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ $isFromTable ? 'bg-indigo-500' : 'bg-gray-400' }}"></div>
                                                    <span class="text-xs font-semibold text-gray-900 truncate">{{ $sourceTable->name }}</span>
                                                    @if($isFromTable)
                                                        <span class="text-[10px] text-gray-500 flex-shrink-0">(this)</span>
                                                    @endif
                                                </div>
                                                <div class="ml-3 flex items-center gap-1">
                                                    <span class="text-[10px] font-mono text-gray-700">{{ $sourceColumn->name }}</span>
                                                    <span class="text-[10px] text-gray-500">({{ $sourceColumn->type }})</span>
                                                </div>
                                            </div>

                                            <!-- Arrow -->
                                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                            </svg>

                                            <!-- Target -->
                                            <div class="flex-1 min-w-0 bg-gray-50 rounded border border-gray-200 p-1.5">
                                                <div class="flex items-center gap-1.5 mb-1">
                                                    <div class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ !$isFromTable ? 'bg-green-500' : 'bg-gray-400' }}"></div>
                                                    <span class="text-xs font-semibold text-gray-900 truncate">{{ $targetTable->name }}</span>
                                                    @if(!$isFromTable)
                                                        <span class="text-[10px] text-gray-500 flex-shrink-0">(this)</span>
                                                    @endif
                                                </div>
                                                <div class="ml-3 flex items-center gap-1">
                                                    <span class="text-[10px] font-mono text-gray-700">{{ $targetColumn->name }}</span>
                                                    <span class="text-[10px] text-gray-500">({{ $targetColumn->type }})</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 p-8 text-center">
                                        <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <h3 class="mt-3 text-sm font-medium text-gray-900">No relationships found</h3>
                                        <p class="mt-1 text-xs text-gray-500">This table doesn't have any relationships yet.</p>
                                    </div>
                                @endforelse
                            </div>
                        </article>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
