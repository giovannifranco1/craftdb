<?php

test('MCP13: integrations page shows local MCP URL, stdio command and no login/OAuth text', function () {
    /** @var TestCase $this */
    $this->autenticated();

    $response = $this->get('/settings/integrations')->assertOk();

    $response
        ->assertSee('/mcp/craftdb', false)
        ->assertSee('claude mcp add --transport http craftdb', false)
        ->assertSee('docker compose exec -T app php artisan mcp:start craftdb', false)
        ->assertSee('"command": "docker"');

    $html = $response->getContent();
    $html = preg_replace('#<(script|style)\b.*?</\1>#is', '', $html);
    $text = html_entity_decode(strip_tags($html));
    foreach (['log in', 'login', 'authorize', 'autoriz', 'token', 'oauth'] as $word) {
        expect(stripos($text, $word))->toBeFalse("página contém '{$word}': ".substr($text, max(0, (int) stripos($text, $word) - 80), 160));
    }
});
