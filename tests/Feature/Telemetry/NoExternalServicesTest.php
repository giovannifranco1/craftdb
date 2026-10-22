<?php

use Illuminate\Support\Env;
use Illuminate\Support\Facades\Route;

test('ZT05 pacotes de telemetria e e-mail em nuvem nao estao instalados', function () {
    expect(class_exists('Laravel\Nightwatch\NightwatchServiceProvider'))->toBeFalse()
        ->and(class_exists('Resend\Laravel\ResendServiceProvider'))->toBeFalse();

    $composer = file_get_contents(base_path('composer.json'));
    expect($composer)->not->toContain('laravel/nightwatch')->not->toContain('resend/resend-laravel');
});

test('ZT06 arquivos de config nao definem servicos em nuvem', function (string $file, string $key) {
    $source = file_get_contents(config_path($file.'.php'));

    expect($source)->not->toMatch("/^\\s*'".preg_quote($key, '/')."'\\s*=>/m");
})->with(function () {
    $keys = ['s3', 'postmark', 'ses', 'resend', 'slack', 'papertrail', 'sqs', 'dynamodb', 'failover'];

    foreach (['filesystems', 'services', 'mail', 'logging', 'queue', 'cache'] as $file) {
        foreach ($keys as $key) {
            // 'failover' so e proibido nos mailers; na fila e um driver local.
            if ($key === 'failover' && $file !== 'mail') {
                continue;
            }

            yield "$file/$key" => [$file, $key];
        }
    }
});

test('ZT06 rota de webhook do Resend nao existe', function () {
    expect(Route::has('resend.webhook'))->toBeFalse();
});

test('ZT07 .env.example e docker-compose sem variaveis de nuvem', function (string $file) {
    expect(file_get_contents(base_path($file)))
        ->not->toMatch('/AWS_|NIGHTWATCH_|RESEND_|POSTMARK_|SLACK_|PAPERTRAIL_/');
})->with(['.env.example', 'docker-compose.yml', '.env.testing']);

test('ZT08 mailer padrao e log sem env', function () {
    $repo = Env::getRepository();
    $original = env('MAIL_MAILER');
    $repo->clear('MAIL_MAILER');
    unset($_ENV['MAIL_MAILER'], $_SERVER['MAIL_MAILER']);
    putenv('MAIL_MAILER');

    try {
        $mail = require config_path('mail.php');
        expect($mail['default'])->toBe('log');
    } finally {
        if ($original === null) {
            putenv('MAIL_MAILER');
            unset($_ENV['MAIL_MAILER'], $_SERVER['MAIL_MAILER']);
        } else {
            putenv('MAIL_MAILER='.$original);
            $_ENV['MAIL_MAILER'] = $_SERVER['MAIL_MAILER'] = $original;
        }
    }
});
