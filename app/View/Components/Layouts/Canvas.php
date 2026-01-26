<?php

namespace App\View\Components\Layouts;

use App\Models\Diagram;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Canvas extends Component
{
    /** @var Collection<int, \App\Models\Relationship> */
    public Collection $relationships;

    public bool $isInitialLoad;
    public ?string $title;

    /**
     * Create a new component instance.
     */
    public function __construct (
        public Diagram $diagram
    )
    {
        $this->relationships = $diagram->relationships()->get();
        $this->isInitialLoad = (bool) $diagram->is_initial_load;

        if ($this->isInitialLoad) {
            $diagram->is_initial_load = 0;
            $diagram->save();
        }

        $this->title = $diagram->slug;

        $this->diagram = $diagram->load('team');
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.layouts.canvas.index');
    }
}
