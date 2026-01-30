<?php

namespace App\Actions\Parse;

use App\Models\Diagram;
use Illuminate\Http\UploadedFile;

abstract class BaseSQLParse
{
    protected Diagram $diagram;

    /**
     * @param array<UploadedFile> $files
     */
    abstract public function exec(array $files, Diagram $diagram): void;

    /**
     * @param UploadedFile $file
     * @return string
     */
    protected function getFileContent(UploadedFile $file): string
    {
        $content = file_get_contents($file->getRealPath());

        return $content !== false ? $content : '';
    }
}
