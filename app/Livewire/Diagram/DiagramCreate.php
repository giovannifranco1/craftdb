<?php

namespace App\Livewire\Diagram;

use App\Actions\Diagram\CreateNewDiagram;
use App\Livewire\Forms\Diagram\ImportForm;
use App\Models\Diagram;
use App\Models\Team\Team;
use App\Rules\Diagram\ImportValidFileType;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class DiagramCreate extends Component
{
    use WithFileUploads;

    /** UI StateForm Import */
    public ImportForm $importForm;

    // UI state $this
    public int $step = 1;

    // UI StateForm $this
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:255')]
    public ?string $description = '';

    #[Validate('required|in:colorful,basic')]
    public ?string $style = null;

    #[Validate('required|in:mysql,postgresql,sqlite')]
    public ?string $database = null;

    /** @return void  */
    public function mount()
    {
        $this->resetWizard();
    }

    public function resetWizard(): void
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
            'style' => 'required|in:colorful,basic',
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
    }

    public function confirmImport(): void
    {
        $this->importForm->confirm();
    }

    public function import()
    {
        $this->validate([
            'file'        => 'required|array',
            'file.*'      => 'file|mimes:sql,json,xml,csv,zip,php|max:10240',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            // Simulate upload progress
            for ($i = 0; $i <= 100; $i += 10) {
                usleep(100000); // 0.1 second delay
            }

            // Store the first file (or process all files)
            $filePaths = [];
            foreach ($this->file as $file) {
                $filePaths[] = $file->store('diagram-imports', 'public');
            }
            $filePath = $filePaths[0] ?? null;

            // Ensure unique slug
            $baseSlug = Str::slug($this->name);
            $slug = $baseSlug;
            $counter = 1;
            while (Diagram::where('slug', $slug)->where('team_id', Team::currentTeam()->getKey())->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            // Create diagram
            $diagram = Diagram::create([
                'name'        => $this->name,
                'description' => $this->description ?: null,
                'team_id'     => Team::currentTeam()->getKey(),
                'slug'        => $slug,
            ]);

            // TODO: Process the file and import the diagram structure
            // This would parse the SQL/JSON/XML/CSV and create tables, columns, relationships

            $this->dispatch('diagram-imported', diagramId: $diagram->id);

            // Redirect to the diagram
            return $this->redirect(route('diagram.show', [
                Team::currentTeam()->getKey(),
                $diagram->slug,
            ]), navigate: true);
        } catch (\Exception $e) {
            $this->addError('file', 'Erro ao importar diagrama: ' . $e->getMessage());
        }
    }

    public function create(CreateNewDiagram $action)
    {
        $validatedData = $this->validate();

        $diagram = $action->exec($validatedData);

        $this->dispatch('diagram-imported', diagramId: $diagram->id);
        $this->modal('diagram-create')->close();

        return $this->redirect(route('diagram.show', [
            Team::currentTeam()->getKey(),
            $diagram->slug,
        ]), navigate: true);
    }

    /**
     * @throws BindingResolutionException
     */
    public function render(): View
    {
        return view('livewire.diagram.diagram-create');
    }
}
