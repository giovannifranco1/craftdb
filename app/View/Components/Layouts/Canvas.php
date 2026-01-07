<?php

namespace App\View\Components\Layouts;

use App\Models\Diagram;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Canvas extends Component
{
    public $relationships;
    public bool $isInitialLoad;
    public ?string $title;

    /**
     * Create a new component instance.
     */
    public function __construct (
        public ?Diagram $diagram
    )
    {
        $this->relationships = $diagram->relationships()->get();

        $this->isInitialLoad = $diagram->is_initial_load;

        if ($this->isInitialLoad) {
            $diagram->is_initial_load = false;
            $diagram->save();
        }

        $this->title = $diagram->slug;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.layouts.canvas');
    }
}
