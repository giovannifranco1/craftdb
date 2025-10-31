<?php

namespace App\View\Components\Table;

use App\Models\Table;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CssTable extends Component
{
    public $color;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public readonly ?Table $table
    ){
        $this->color = $table?->color ?? Table::COLOR_DEFAULT;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.table.css-table');
    }
}
