<?php

use App\Actions\Diagram\CreateNewDiagram;
use App\Livewire\Forms\Diagram\ImportForm;
use App\Rules\Diagram\ImportValidFileType;
use Livewire\Attributes\Transition;
use Livewire\Attributes\Validate;
use App\Livewire\BaseComponent;
use Livewire\WithFileUploads;

new class extends BaseComponent {
    use WithFileUploads;

    /** UI StateForm Import */
    public ImportForm $importForm;

    // UI state $this
    /** @var int<1,2> */
    public int $step = 1;

    // UI StateForm $this
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:255')]
    public ?string $description = '';

    #[Validate('required|in:colorful,neutral')]
    public ?string $style = null;

    #[Validate('required|in:mysql,postgresql,sqlite')]
    public ?string $database = null;

    public function mount(): void
    {
        $this->resetUIState();
    }

    #[Transition(skip: true)]
    public function resetUIState(): void
    {
        $this->step = 1;
        $this->name = '';
        $this->description = '';
        $this->style = null;
        $this->database = null;

        $this->importForm->hasImport = false;
        $this->importForm->type = null;
        $this->importForm->file = [];
    }

    public function nextStep(): void
    {
        $this->validate([
            'name'  => 'required|string|max:255',
            'style' => 'required|in:colorful,neutral',
            'description' => 'nullable|string|max:255',
        ]);

        $this->step = 2;
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    /**Hook Updated importForm.Type */
    public function updatedImportFormType(): void
    {
        $this->importForm->file = [];
    }

    /**Hook Updated importForm.File */
    public function updatedImportFormFile(): void
    {
        $this->validate([
            'importForm.file'   => 'nullable|array',
            'importForm.file.*' => [
                'file',
                new ImportValidFileType,
            ],
        ]);

    }

    public function cancelFileImport(): void
    {
        $this->importForm->cancelFile();
    }

    public function cancelImport(): void
    {
        $this->importForm->cancel();
        $this->dispatch('diagram-import::hide');
    }

    public function cancelCreate(): void
    {
        $this->resetUIState();
        $this->modal('diagram-create')->close();
    }

    public function confirmImport(): void
    {
        $this->importForm->confirm();
        $this->dispatch('diagram-import::hide');
    }

    public function create(CreateNewDiagram $action): void
    {
        $validatedData = $this->validate();

        $diagram = $action->exec($validatedData);

        $this->modal('diagram-create')->close();

        session()->flash('message', __("Diagram created successfully."));

        $this->redirect(route('diagram.show', [
            $diagram->team_id,
            $diagram->slug,
        ]));
    }



};
