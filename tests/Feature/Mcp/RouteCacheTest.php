<?php

use Symfony\Component\Process\Process;

test('MCP11: php artisan route:cache succeeds without duplicate route names', function () {
    $tmp = tempnam(sys_get_temp_dir(), 'routes-');
    @unlink($tmp);
    $real = base_path('bootstrap/cache/routes-v7.php');
    $existedBefore = file_exists($real);

    try {
        $process = new Process([PHP_BINARY, 'artisan', 'route:cache', '--no-ansi'], base_path(), [
            'APP_ROUTES_CACHE' => $tmp,
            'APP_ENV' => 'testing',
        ]);
        $process->setTimeout(120);
        $process->run();

        expect($process->getExitCode())->toBe(0, $process->getOutput().$process->getErrorOutput());
        expect(file_exists($tmp))->toBeTrue();
        expect(file_exists($real))->toBe($existedBefore);
    } finally {
        @unlink($tmp);
        if (! $existedBefore) {
            @unlink($real);
        }
    }
});
