<?php

namespace App\View\Components\Layouts;

use App\Models\Diagram;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AppWorkspace extends Component
{
    public $relationships;
    /**
     * Create a new component instance.
     */
    public function __construct (
        public ?Diagram $diagram
    )
    {
        $this->relationships = $this->diagram->relationships()->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.layouts.app-workspace');
    }
}
