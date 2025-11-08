<?php

namespace App\Livewire\Diagram;

use App\Models\Diagram;
use Illuminate\Database\Eloquent\Collection;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;

#[Lazy]
class TableList extends Component
{
    public Diagram $diagram;

    public function mount(Diagram $diagram)
    {
        $this->diagram = $diagram;
    }

    #[Computed]
    public function getTables(): Collection
    {
        return $this->diagram->tables()
            ->withForeignCount()
            ->withPrimaryCount()
            ->withCount('columns')
            ->get();
    }

    #[On('add-table')]
    public function addTable(): void
    {
        $this->diagram->tables()->create([
            'name' => 'teste',
            'x' => 0,
            'y' => 0,
        ]);

        $count = $this->diagram->tables()->count();

        $this->dispatch('alert', message: "Table $count added successfully.");
    }


    public function placeholder()
    {
        return <<<'HTML'
            <div></div>
        HTML;
    }

    public function render()
    {
        return view('livewire.diagram.table-list');
    }
}
