<?php

namespace App\Livewire\Diagram;

use App\Models\Diagram;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
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
