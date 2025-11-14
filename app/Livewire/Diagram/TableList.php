<?php

namespace App\Livewire\Diagram;

use App\Actions\Diagram\AddRelationship;
use App\Models\Diagram;
use App\Support\Helpers;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;

#[Lazy]
class TableList extends Component
{
    public Diagram $diagram;

    /** @var array<string, string>  */
    protected $listeners = [
        'table::color-saved'  => '$refresh',
        'table::column-added' => '$refresh',
        'table::removed'      => '$refresh',
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
    #[Computed]
    public function getTables(): Collection
    {
        $tables = $this->diagram->tables();

        $tables = $tables->withForeignCount();
        $tables = $tables->withPrimaryCount();

        return $tables
            ->withCount('columns')
            ->with(['diagram'])
            ->get();
    }

    #[On('add-table')]
    public function addTable(int $x = 100, int $y = 100): void
    {
        $this->diagram->tables()->create([
            'name'  => 'teste',
            'x'     => $x,
            'y'     => $y,
            'color' => Helpers::getRandomColor($this->diagram->style),
        ]);

        $count = $this->diagram->tables()->count();

        $this->dispatch('alert', message: "Table $count added successfully.");
    }

    /**
     * @param int $diagramId
     * @param array<int, array{id:int, x:int, y:int}> $tables
     */
    #[On('save-tables-positions')]
    public function saveTablesPositions(int $diagramId, array $tables): void
    {
        if ($this->diagram->id != $diagramId) {
            return;
        }

        foreach ($tables as $tableData) {
            $this->diagram->tables()
                ->where('id', $tableData['id'])
                ->update([
                    'x' => (int) $tableData['x'],
                    'y' => (int) $tableData['y'],
                ]);
        }

        $this->dispatch('alert', message: 'Posições das tabelas salvas com sucesso!');
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
