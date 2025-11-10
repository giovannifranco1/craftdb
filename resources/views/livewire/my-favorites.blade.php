<x-slot:title>My Favorites</x-slot:title>

<div>

    <livewire:diagram.table-side-editor/>
    <livewire:diagram.table-relationships-side-editor/>

    <flux:button @click.stop="$dispatch('table-side-editor.open')" variant="primary">Table Side</flux:button>
    <flux:button @click.stop="$dispatch('table-relationships-side-editor.open')" variant="primary">Table Side relationships</flux:button>

    <div class="mt-10">
        <div class="hs-dropdown [--placement:top] border-1 relative inline-flex w-max-xl">
            <button id="hs-dropdown-unstyled" type="button" class="hs-dropdown-toggle inline-flex justify-center items-center gap-x-2" aria-expanded="false" aria-label="Menu">
            Actions
            </button>

            <div class="hs-dropdown-menu w-100 bg-accent transition-[opacity,margin] hs-dropdown-open:border-red-500 duration hs-dropdown-open:opacity-100 opacity-0 hidden z-10 mt-2 min-w-60 text-whi" role="menu" aria-labelledby="hs-dropdown-unstyled">
                <a class="" href="#">Newsletter</a>
                <a class="" href="#">Purchases</a>
                <a class="" href="#">Downloads</a>
                <a class="" href="#">Team Account</a>
            </div>
        </div>
    </div>
</div>
