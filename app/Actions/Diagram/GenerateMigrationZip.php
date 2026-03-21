<?php

namespace App\Actions\Diagram;

use RuntimeException;
use ZipArchive;

class GenerateMigrationZip
{
    /**
     * @param array<int, array{filename: string, content: string}> $files
     */
    public function exec(array $files): string
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'migrations_') . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create ZIP archive.');
        }

        foreach ($files as $file) {
            $zip->addFromString($file['filename'], $file['content']);
        }

        $zip->close();

        return $tmpPath;
    }
}
