<?php

namespace App\View\Components\Diagram\Table\UI;

use App\Models\Table;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CssTable extends Component
{
    public string $color;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public readonly ?Table $table
    ){
        $this->color = $table->color ?? Table::COLOR_DEFAULT;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.diagram.table.ui.css-table');
    }
}
