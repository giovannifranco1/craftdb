<?php

use App\Concerns\HandlesDiagramAuthorizationWithAlertError;
use App\Models\Column;
use App\Models\Diagram;
use App\Models\Table as TableModel;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    use HandlesDiagramAuthorizationWithAlertError;

    /** @var array<string, string>  */
    protected $listeners = [
        'table::column-saved' => 'refreshTableWithCounts',
        'table-side-editor::column-added'   => 'refreshTableWithCounts',
        'table-side-editor::column-saved'   => 'refreshTableWithCounts',
        'table-side-editor::column-deleted' => 'refreshTableWithCounts',
        'table-side-editor::column-updated' => 'refreshTableWithCounts',
    ];

    #[Locked]
    public TableModel $table;

    #[Locked]
    public int $diagramId;

    public int $maxColumns;

    #[Validate('required|string|max:255|min:1')]
    public ?string $name = null;

    /** @var array<string, string>  */
    public $relationship = [
        'from_column_id' => '',
        'to_column_id'   => '',
        'type'           => '',
    ];

    public function mount(TableModel $table): void
    {
        $this->table = $table;
        $this->diagramId = $table->diagram_id;
        $this->name = $table->name;
        $this->maxColumns = TableModel::MAX_COLUMNS;
    }

    /**
     * @return Collection<int,Column>
     */
    #[Computed()]
    public function getColumns(): Collection
    {
        return $this->table->columns;
    }

    public function renameTable(): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->table->diagram)) {
            return;
        }

        $this->validateOnly('name');

        $this->table->name = $this->name ?? '';
        $this->table->save();

        $this->dispatch('table::renamed');
        $this->refreshTableWithCounts();
    }

    public function resetName(): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->table->diagram)) {
            return;
        }

        $this->name = $this->table->name;
        $this->refreshTableWithCounts();
    }

    public function setColor(string $color): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->table->diagram)) {
            $this->dispatch('table::color-updated-error');
            return;
        }

        if (!Helpers::isColorValid($color)) return;

        $this->table->color = $color;
        $this->table->save();

        $this->refreshTableWithCounts();
        $this->dispatch('table::color-updated');
    }

    public function renameColumn(Column $column, string $newName): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->table->diagram)) {
            return;
        }

        $validated = Validator::make(['newName' => $newName], ['newName' => 'required|string|max:255|min:1']);

        if ($validated->fails()) {
            $this->dispatch('alert', message: __('Invalid name'), type: 'error');
            $this->dispatch('table::column-renamed');
            return;
        }

        $column->name = $validated['newName'];
        $column->save();

        $this->refreshTableWithCounts();
        $this->dispatch('table::column-renamed');
    }

    public function addColumn(): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->table->diagram)) {
            return;
        }

        if ($this->table->columns()->count() >= $this->maxColumns) {
            $this->dispatch('alert', message: __('You have reached the maximum number of columns for this table.'), type: 'error');
            return;
        }

        $maxOrder = $this->table->columns()->max('order');
        $newOrder = Helpers::calculateNextOrder($maxOrder);

        $baseColumn = [
            'name'  => 'new_column',
            'type'  => 'varchar',
            'order' => $newOrder,
            'is_notnull' => true,
        ];

        $this->table->columns()->create($baseColumn);
        $this->refreshTableWithCounts();
        $this->dispatch('table::column-added');
    }

    #[On('table::relationship-added')]
    public function refreshTableWithCounts(): void
    {
        $table = $this->table->fresh([
            'diagram',
            'columns' => fn ($query) => $query->orderBy('order'),
        ]);

        if ($table === null) {
            throw new LogicException('Table não existe mais');
        }

        $this->table = $table->loadCount([
            'columns',
            'columns as primary_count' => fn ($q) => $q->where('is_primary', true),
            'columns as foreign_count' => fn ($q) => $q->where('is_foreign', true),
        ]);
    }

    public function remove(): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->table->diagram)) {
            return;
        }

        DB::transaction(function () {
            $this->table->relationships()->delete();
            $this->table->columns()->delete();
            $this->table->delete();
        });

        $this->dispatch('alert', message: __('Table Removed'));
        $this->dispatch('table::removed');
        $diagram = Diagram::find($this->diagramId);
        $relationships = $diagram ? $diagram->relationships()->get() : collect();
        $this->dispatch('relationships::fresh', relationships: $relationships);
    }

    #[On('table::move.{table.id}')]
    public function updatePosition(int $x, int $y): void
    {
        if(Gate::denies('update', $this->table->diagram)) {
            return;
        }

       $this->table->update([
            'x' => $x,
            'y' => $y,
        ]);

        $this->refreshTableWithCounts();
    }
};
