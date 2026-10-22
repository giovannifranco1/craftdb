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

    public function getTableCountProperty(): int
    {
        if ($this->importForm->type !== 'migration' || empty($this->importForm->file)) {
            return 0;
        }

        $count = 0;
        foreach ($this->importForm->file as $file) {
            $ext = strtolower($file->getClientOriginalExtension());
            if ($ext === 'php') {
                $content = file_get_contents($file->getRealPath());
                if ($content !== false) {
                    $count += $this->countCreatesInContent($content);
                }
            } elseif ($ext === 'zip') {
                $count += $this->countCreatesInZip($file->getRealPath());
            }
        }
        return $count;
    }

    private function countCreatesInContent(string $content): int
    {
        preg_match_all('/Schema\s*::\s*create\s*\(/', $content, $matches);
        return count($matches[0]);
    }

    private function countCreatesInZip(string $path): int
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) return 0;
        $count = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name !== false && str_ends_with($name, '.php')) {
                $zipContent = $zip->getFromIndex($i);
                if ($zipContent !== false) {
                    $count += $this->countCreatesInContent($zipContent);
                }
            }
        }
        $zip->close();
        return $count;
    }

    public function import(ImportToExistingDiagram $action): void
    {
        $this->authorizeDiagram('update');

        $this->importForm->hasImport = true;
        $this->importForm->validate();

        $action->exec($this->diagram, $this->importForm->file, $this->importForm->type ?? '');

        Flux::modal('diagram-canvas-import')->close();

        $this->importForm->cancelFile();
        $this->importForm->reset();
        $this->importForm->hasImport = false;

        $this->dispatch('diagram::imported');
        $this->dispatch('relationships::fresh', relationships: $this->diagram->relationships()->get());

        $this->alert(__('Import completed successfully.'), type: 'success');
    }
};
