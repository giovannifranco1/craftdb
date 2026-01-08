<?php

namespace App\Livewire\Diagram;

use App\Actions\Diagram\AddRelationship;
use App\Concerns\HandlesDiagramAuthorizationWithAlertError;
use App\Models\Diagram;
use App\Support\Helpers;
use DB;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class TableList extends Component
{
    use HandlesDiagramAuthorizationWithAlertError;

    #[Locked]
    public Diagram $diagram;

    /** @var array<string, string>  */
    protected $listeners = [
        'table::color-updated'                => '$refresh',
        'table::column-added'                 => '$refresh',
        'table::removed'                      => '$refresh',
        'table-side-editor::column-saved'     => '$refresh',
        'table-side-editor::column-deleted'   => '$refresh',
        'table-side-editor::column-updated'   => '$refresh',
    ];

    public function mount(Diagram $diagram): void
    {
        $this->diagram = $diagram;

        $offsets = Helpers::getPositionCanvasByDiagram($this->diagram);

        $this->dispatch('table-list::inicializeted', ...$offsets);
    }

    /**
     * @return Collection<int, \App\Models\Table>
     */
    #[Computed(persist: true)]
    public function getTables(): Collection
    {
        $tables = $this->diagram->tables();

        $tables = $tables->withForeignCount();
        $tables = $tables->withPrimaryCount();

        return $tables
            ->withCount('columns')
            ->with(['diagram'])
            ->with(['columns' => fn(QueryBuilder $query): QueryBuilder => $query->orderBy('order')])
            ->with(['foreignKeys' => fn(QueryBuilder $query): QueryBuilder => $query->orderByDesc('order')])
            ->get();
    }

    #[On('table::add')]
    public function addTable(int $x = 100, int $y = 100): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->diagram)) {
            return;
        }

        $table = $this->diagram->tables()->create([
            'name'  => 'teste',
            'x'     => $x,
            'y'     => $y,
            'color' => Helpers::getRandomColor($this->diagram->style),
        ]);

        $count = $this->diagram->tables()->count();

        $this->dispatch('alert', message: "Table $count added successfully.");
        $this->dispatch('table::added', tableId: $table->id);
    }

    /**
     * @param int $diagramId
     * @param array<int, array{id:int, x:int, y:int}> $tables
     */
    #[On('save-tables-positions')]
    public function saveTablesPositions(int $diagramId, array $tables): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->diagram)) {
            return;
        }

        if ($this->diagram->id != $diagramId) {
            return;
        }

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

    public function placeholder(): string
    {
        return <<<'HTML'
            <div></div>
        HTML;
    }

    #[On('table::add-relationship')]
    public function addRelationship(int $fromTableId, int $toTableId, string $type, AddRelationship $action): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->diagram)) {
            return;
        }

        $data = validator()->make([
            'fromTableId' => $fromTableId,
            'toTableId'   => $toTableId,
            'type'        => $type,
        ], [
            'fromTableId' => ['required', 'integer', 'exists:table,id'],
            'toTableId'   => ['required', 'integer', 'exists:table,id'],
            'type'        => ['required', 'string', 'in:1:n,1:1,n:n,n:1'],
        ])->validated();

        $action->exec($data);

        $this->dispatch('table::relationship-added');
        $this->dispatch('alert', message: "Relationship added");
        $this->dispatch('relationships::fresh', relationships: $this->diagram->relationships()->get());
    }

    public function render(): View
    {
        return view('livewire.diagram.table-list');
    }
}
