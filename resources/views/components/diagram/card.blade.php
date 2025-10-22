<a href="{{ route('diagram.show', $diagram->name) }}">
    <div class="card bg-base-100 w-100 shadow-sm">

        <div class="card-body">
            <div class="bg-blue-100 p-3 w-12 rounded-sm">
                <flux:icon.table-cells class="text-blue-600" />
            </div>

            <h2 class="card-title">{{ $diagram->name }}</h2>

            @if (!is_null($diagram->description))
                <p>{{ $diagram->description }}</p>
            @endif

            <small class="font-normal">Criado em {{ $diagram->updated_at->format('d/m/y h:i') }}</small>

            <flux:button  variant="primary" color="blue">
                <span class="font-bold">Abrir Diagrama</span>
            </flux:button>


        </div>
    </div>
</a>
