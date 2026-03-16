<?php

use App\Actions\Diagram\AddRelationship;
use App\Actions\Diagram\UpdateResizePositionTable;
use App\Actions\Diagram\UpdateTable;
use App\Enums\RelationshipType;
use App\Livewire\CanvasComponent;
use App\Models\Diagram;
use App\Models\Table;
use App\Support\DiagramLimits;
use App\Support\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Defer;
use Livewire\Attributes\On;

new #[Defer(bundle: true)] class extends CanvasComponent {
    /** @var array<string, string>  */
    protected $listeners = [
        'diagram::imported'                   => '$refresh',
        'table::removed'                      => '$refresh',
        'table::color-updated'                => '$refresh',
        'table::column-added'                 => '$refresh',
        'table-side-editor::column-saved'     => '$refresh',
        'table-side-editor::column-deleted'   => '$refresh',
        'table-side-editor::column-updated'   => '$refresh',
    ];

    public function mount(Diagram $diagram): void
    {
        $this->diagramId = $diagram->id;

        $offsets = Helpers::getPositionCanvasByDiagram($diagram);

        $this->dispatch('table-list::inicializeted', ...$offsets);
    }

    /**
     * @return Collection<int, \App\Models\Table>
     */
    #[Computed]
    public function tables(): Collection
    {
        $tables = $this->diagram->tables();

        $tables = $tables->withForeignCount();
        $tables = $tables->withPrimaryCount();
        $tables = $tables->withColumnsCount();

        return $tables
            ->withExists('columns')
            ->with(['diagram'])
            ->with(['columns' => fn(QueryBuilder $query): QueryBuilder
                => $query->orderBy('order')
                    ->withExists(['relationshipsFrom', 'relationshipsTo'])
            ])
            ->with(['foreignKeys' => fn(QueryBuilder $query): QueryBuilder => $query->orderByDesc('order')])
            ->get();
    }

    #[On('table::add')]
    public function addTable(int $x = 100, int $y = 100): void
    {
        $this->authorizeDiagram('update');

        $owner = $this->diagram->team->owner;
        $limits = DiagramLimits::forUser($owner);

        if ($this->diagram->tables()->count() >= $limits['table_limit']) {
            $this->dispatch('alert', message: __('You have reached the maximum number of tables for this diagram.'), type: 'error');
            return;
        }

        $table = $this->diagram->tables()->create([
            'name'  => 'new_table',
            'x'     => $x,
            'y'     => $y,
            'color' => Helpers::getRandomColor($this->diagram->style),
        ]);

        $count = $this->diagram->tables()->count();

        $this->dispatch('alert', message: __('Table :count added successfully.', ['count' => $count]));
        $this->dispatch('table::added', tableId: $table->id);
    }

    /**
     * @param int $diagramId
     * @param array<int, array{id:int, x:int, y:int}> $tables
     */
    #[On('save-tables-positions')]
    public function saveTablesPositions(int $diagramId, array $tables): void
    {
        $this->authorizeDiagram('update');

        //validate tables
        $validator = validator()->make($tables, [
            '*.id' => ['required', 'integer', 'exists:table,id'],
            '*.x' => ['required', 'integer'],
            '*.y' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            $this->dispatch('alert', message: $validator->errors()->first(), type: 'error');
            return;
        }

        // validate if the tables are owned by the diagram
        $validator->after(function ($validator) use ($tables) {
            foreach ($tables as $table) {
                if ($this->diagram->tables()->where('id', $table['id'])->doesntExist()) {
                    $validator->errors()->add('tables', 'The table is not owned by the diagram.');
                }
            }
        });

        DB::transaction(function () use ($tables) {
            foreach ($tables as $t) {
                $this->diagram->tables()
                    ->where('id', $t['id'])
                    ->update([
                        'x' => $t['x'],
                        'y' => $t['y'],
                    ]);
            }

            $this->diagram->update(['updated_at' => now()]);
        });

        $this->dispatch('table-list::position-updated', updated_at: $this->diagram->updated_at?->diffForHumans());
    }

    #[On('table::add-relationship')]
    public function addRelationship(int $fromTableId, int $toTableId, RelationshipType $type, AddRelationship $action, UpdateResizePositionTable $updateResizePositionTableAction): void
    {
        $this->authorizeDiagram('update');

        if ($type === RelationshipType::MANY_TO_MANY) $this->validateTableLimit();

        /** @var array{fromTableId: int, toTableId: int, type: string} $data */
        $data = validator()->make([
            'fromTableId' => $fromTableId,
            'toTableId'   => $toTableId,
            'type'        => $type->value,
        ], [
            'fromTableId' => ['required', 'integer', 'exists:table,id'],
            'toTableId'   => ['required', 'integer', 'exists:table,id'],
            'type'        => ['required', 'string', 'in:1:n,1:1,n:n,n:1'],
        ])->validated();

        $action->exec($data);
        $updateResizePositionTableAction->exec(Table::find($toTableId));

        $this->dispatch("table::relationship-added.{$fromTableId}");
        $this->dispatch("table::relationship-added.{$toTableId}");
        $this->dispatch('alert', message: "Relationship added");
        $this->dispatch('relationships::fresh', relationships: $this->diagram->relationships()->get());
    }
};
