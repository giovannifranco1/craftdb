<?php

use App\Actions\Diagram\UpdateDiagram;
use App\Models\Diagram;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    public Diagram $diagram;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:255')]
    public ?string $description = '';

    #[Validate('required|in:colorful,neutral')]
    public string $style = '';

    #[Validate('required|in:mysql,postgresql,sqlite')]
    public string $database = '';

    public string $originalStyle = '';
    public string $originalDatabase = '';
    public bool $showWarning = false;

    /** @var int<1,2> */
    public int $step = 1;

    /** @var array<string, string> */
    protected $listeners = ['open-diagram-edit-modal' => 'openModal'];

    public function mount(Diagram $diagram): void
    {
        $this->diagram = $diagram;
        $this->name = $diagram->name;
        $this->description = $diagram->description ?? '';
        $this->style = $diagram->style;
        $this->database = $diagram->database ?? 'mysql';
        $this->originalStyle = $this->style;
        $this->originalDatabase = $this->database;
    }

    public function updatedStyle(): void
    {
        $this->checkForWarning();
    }

    public function updatedDatabase(): void
    {
        $this->checkForWarning();
    }

    private function checkForWarning(): void
    {
        $this->showWarning =
            $this->style !== $this->originalStyle ||
            $this->database !== $this->originalDatabase;
    }

    public function openModal(int $diagramId): void
    {
        if ($diagramId === $this->diagram->id) {
            $this->diagram->refresh();
            $this->mount($this->diagram);
            $this->step = 1;
            $this->modal('diagram-edit-' . $this->diagram->id)->show();
        }
    }

    public function nextStep(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $this->step = 2;
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function cancelEdit(): void
    {
        $this->reset(['name', 'description', 'style', 'database', 'showWarning', 'step']);
        $this->name = $this->diagram->name;
        $this->description = $this->diagram->description ?? '';
        $this->style = $this->diagram->style;
        $this->database = $this->diagram->database ?? 'mysql';
        $this->originalStyle = $this->style;
        $this->originalDatabase = $this->database;
        $this->step = 1;
        $this->modal('diagram-edit-' . $this->diagram->id)->close();
    }

    public function update(UpdateDiagram $action): void
    {
        $validatedData = $this->validate();

        $action->exec($this->diagram, $validatedData);

        $this->modal("diagram-edit-{$this->diagram->id}")->close();
        $this->modal("confirm-diagram-edit-{$this->diagram->id}")->close();

        $this->reset('showWarning', 'step');

        $this->dispatch('alert', message: __("Diagram updated successfully."));
        $this->dispatch('diagram-updated');
    }
};
