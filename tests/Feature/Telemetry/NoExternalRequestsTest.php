<?php

use App\Models\Diagram;

/**
 * Extrai URLs de recursos carregados automaticamente pelo navegador
 * (nao inclui <a href> nem namespaces SVG) que apontam para fora do APP_URL.
 *
 * @return array<int, string>
 */
function externalResourceUrls(string $html): array
{
    $urls = [];

    // <link|script|img|source|iframe ... src=/href=; the regex tolerates `>` inside quoted attributes.
    preg_match_all('/<(?:link|script|img|source|iframe)\b(?:"[^"]*"|\'[^\']*\'|[^>"\'])*>/i', $html, $tags);
    foreach ($tags[0] as $tag) {
        preg_match_all('/\b(?:src|href)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $tag, $m, PREG_SET_ORDER);
        foreach ($m as $match) {
            $urls[] = ($match[1] ?? '') . ($match[2] ?? '') . ($match[3] ?? '');
        }
    }

    // <meta property="og:*" content="...">
    preg_match_all('/<meta\b[^>]*property\s*=\s*["\']og:[^"\']*["\'][^>]*>/i', $html, $metas);
    foreach ($metas[0] as $meta) {
        if (preg_match('/\bcontent\s*=\s*["\']([^"\']*)["\']/i', $meta, $m)) {
            $urls[] = $m[1];
        }
    }

    // @import / url() em <style> inline e atributos style
    preg_match_all('/@import\s+(?:url\()?["\']?([^"\')\s;]+)/i', $html, $m);
    array_push($urls, ...$m[1]);
    preg_match_all('/url\(\s*["\']?([^"\')\s]+)/i', $html, $m);
    array_push($urls, ...$m[1]);

    $appUrl = rtrim((string) config('app.url'), '/');

    return array_values(array_filter($urls, function (string $url) use ($appUrl) {
        $url = html_entity_decode(trim($url));

        if (! preg_match('#^(https?:)?//#i', $url)) {
            return false;
        }

        if ($url === $appUrl) {
            return false;
        }

        return ! (str_starts_with($url, $appUrl) && in_array($url[strlen($appUrl)], ['/', '?', '#'], true));
    }));
}

function expectNoExternalResources(string $html): void
{
    expect(externalResourceUrls($html))->toBe([]);
}

beforeEach(function () {
    /** @var \Tests\TestCase $this */
    $this->autenticated();
});

it('ZT01: app pages reference no external resources', function (string $path) {
    $response = $this->get($path);

    expectNoExternalResources($response->getContent());
})->with(['/dashboard', '/settings/profile', '/settings/workspaces', '/rota-inexistente']);

it('ZT01: diagram page references no external resources', function () {
    $diagram = Diagram::factory()->create(['team_id' => $this->team->id]);

    $response = $this->get(route('diagram.show', ['team_id' => $this->team->id, 'diagram' => $diagram->slug]));

    $response->assertOk();
    expectNoExternalResources($response->getContent());
});

it('ZT01: error pages reference no external resources', function (string $view) {
    expectNoExternalResources(view($view)->render());
})->with(['errors.404', 'errors.500', 'errors.503']);

it('ZT01: mcp authorize page references no external resources', function () {
    $html = view('mcp.authorize', [
        'user' => $this->user,
        'client' => (object) ['id' => 'client-1', 'name' => 'Test Client'],
        'authToken' => 'token',
        'scopes' => [],
        'request' => request(),
    ])->render();

    expectNoExternalResources($html);
});

it('ZT03: landing has no external hosts, goadopt or craftdb.app (also in production)', function () {
    $originalEnv = app()['env'];

    try {
        foreach (['testing', 'production'] as $env) {
            app()['env'] = $env;
            expect(app()->environment($env))->toBeTrue();

            $html = $this->get('/features')->assertOk()->getContent();

            expectNoExternalResources($html);
            expect(strtolower($html))->not->toContain('goadopt')->not->toContain('craftdb.app');
        }
    } finally {
        app()['env'] = $originalEnv;
    }
});

it('ZT02: compiled css bundles fonts locally via @font-face', function () {
    $manifestPath = public_path('build/manifest.json');
    if (! file_exists($manifestPath)) {
        $this->markTestSkipped('public/build/manifest.json not found (run npm run build).');
    }

    $manifest = json_decode(file_get_contents($manifestPath), true);
    $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
    expect($cssFile)->not->toBeNull();

    $css = file_get_contents(public_path("build/{$cssFile}"));

    foreach (['Poppins', 'Outfit', 'Kalam', 'Space Mono'] as $family) {
        expect($css)->toMatch('/@font-face\s*\{[^}]*font-family:\s*["\']?'.preg_quote($family, '/').'["\']?\s*;[^}]*url\(\s*["\']?(?!https?:|\/\/)[^"\')\s]*\.woff2/i');
    }
});

it('ZT04: compiled css has no external url()/@import', function () {
    $manifest = public_path('build/manifest.json');
    if (! file_exists($manifest)) {
        $this->markTestSkipped('public/build/manifest.json not found (run npm run build).');
    }

    foreach (glob(public_path('build/assets/*.css')) as $file) {
        expect(file_get_contents($file))
            ->not->toMatch('#url\(\s*["\']?(https?:)?//#i')
            ->not->toMatch('#@import\s+(url\()?["\']?(https?:)?//#i');
    }
});

it('ZT01: helper detects external resources and ignores allowed ones', function () {
    config(['app.url' => 'http://localhost']);

    $caught = [
        'unquoted' => ['<link rel=stylesheet href=https://x.com/a.css>', 'https://x.com/a.css'],
        'gt in attr' => ['<link data-x="a>b" href="https://x.com/a.css">', 'https://x.com/a.css'],
        'host prefix' => ['<script src="http://localhost.evil.com/x.js"></script>', 'http://localhost.evil.com/x.js'],
    ];
    foreach ($caught as $name => [$html, $url]) {
        expect(externalResourceUrls($html))->toBe([$url], $name);
    }

    $ignored = [
        '<link href="http://localhost/build/app.css">',
        '<link href="http://localhost?x=1">',
        '<a href="https://github.com">x</a>',
        '<svg xmlns="http://www.w3.org/2000/svg"></svg>',
    ];
    foreach ($ignored as $html) {
        expect(externalResourceUrls($html))->toBe([], $html);
    }
});
