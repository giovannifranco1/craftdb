<?php

use App\Actions\Diagram\ImportToExistingDiagram;
use App\Livewire\CanvasComponent;
use App\Livewire\Forms\Diagram\ImportForm;
use App\Models\Diagram;
use App\Rules\Diagram\ImportValidFileType;
use Flux\Flux;
use Livewire\Attributes\Locked;
use Livewire\WithFileUploads;

new class extends CanvasComponent {
    use WithFileUploads;

    public ImportForm $importForm;

    #[Locked]
    public int $diagramId;

    public function mount(Diagram $diagram): void
    {
        $this->diagramId = $diagram->id;
    }

    public function updatedImportFormType(): void
    {
        $this->importForm->file = [];
    }

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
        $this->importForm->cancelFile();
        $this->importForm->reset();
        $this->importForm->hasImport = false;
        Flux::modal('diagram-canvas-import')->close();
    }

    public function import(ImportToExistingDiagram $action): void
    {
        $this->authorizeDiagram('update');

        $this->importForm->hasImport = true;
        $this->importForm->validate();

        $this->validateTableLimit();

        $action->exec($this->diagram, $this->importForm->file, $this->importForm->type);

        Flux::modal('diagram-canvas-import')->close();

        $this->importForm->cancelFile();
        $this->importForm->reset();
        $this->importForm->hasImport = false;

        $this->dispatch('diagram::imported');
        $this->dispatch('relationships::fresh', relationships: $this->diagram->relationships()->get());

        $this->alert(__('Import completed successfully.'), type: 'success');
    }
};
