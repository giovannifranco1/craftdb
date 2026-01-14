<?php

namespace App\Livewire\Forms\Diagram;

use App\Rules\Diagram\ImportValidFileType;
use Flux\Flux;
use Livewire\Form;

class ImportForm extends Form
{
    /** @var 'file'|'migration'|null */
    public ?string $type = null;

    /**
     * @var array<int, \Illuminate\Http\UploadedFile>
     * @phpstan-var list<\Illuminate\Http\UploadedFile>
     */
    public array $file = [];

    /**
     * @var bool
     * @phpstan-var bool
     */
    public bool $hasImport = false;


    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $rules = [
            'file' => 'nullable|array',
            'file.*' => [
                'file',
                new ImportValidFileType
            ],
            'type' => 'nullable|in:sql,migration'
        ];

        if ($this->hasImport) {
            $rules = array_merge($rules, [
                'file' => 'required|array',
                'type' => 'required|in:sql,migration'
            ]);
        }

        return $rules;
    }

    /**
     * Cancel File
     */
    public function cancelFile(): void
    {
        $this->file = [];
    }

    /**
     * Cancel Import
     */
    public function cancel(): void
    {
        $this->cancelFile();
        $this->hasImport = false;
        Flux::modal('diagram-import')->close();
    }

    /**
     * Confirm import
     */
    public function confirm(): void
    {
        $this->hasImport = true;

        $this->validate();

        Flux::modal('diagram-import')->close();
    }

    /**
     * Remove a file from the import list.
     *
     * @phpstan-param int|null $index The index of the file to remove. If null, all files will be removed.
     * @return void
     */
    public function removeFile(?int $index = null): void
    {
        if ($index !== null) {
            $files = $this->file;
            unset($files[$index]);
            $this->file = array_values($files); // Reindex array
        } else {
            $this->file = [];
        }
    }
}
