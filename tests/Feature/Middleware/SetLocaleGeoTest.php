<?php

test('login page is in Portuguese when CF-IPCountry is BR behind trusted proxy', function () {
    config(['app.cloudflare_proxy' => true]);

    $response = $this->withHeader('CF-IPCountry', 'BR')->get(route('login'));

    $response->assertStatus(200);
    $response->assertSee('Bem-vindo de volta', false);
});

test('register page is in Portuguese when CF-IPCountry is BR behind trusted proxy', function () {
    config(['app.cloudflare_proxy' => true]);

    $response = $this->withHeader('CF-IPCountry', 'BR')->get(route('register'));

    $response->assertStatus(200);
    $response->assertSee('Criar uma conta', false);
});

test('login page stays in English when CF-IPCountry is not BR', function () {
    config(['app.cloudflare_proxy' => true]);

    $response = $this->withHeader('CF-IPCountry', 'US')->get(route('login'));

    $response->assertStatus(200);
    $response->assertSee('Welcome back', false);
});

test('login page ignores forged CF-IPCountry when not behind trusted proxy', function () {
    config(['app.cloudflare_proxy' => false]);

    $response = $this->withHeader('CF-IPCountry', 'BR')->get(route('login'));

    $response->assertStatus(200);
    $response->assertSee('Welcome back', false);
});

test('landing index is in Portuguese when CF-IPCountry is BR behind trusted proxy', function () {
    config(['app.cloudflare_proxy' => true]);

    $response = $this->withHeader('CF-IPCountry', 'BR')->get(route('landing.index'));

    $response->assertStatus(200);
    $response->assertSee('A forma mais visual', false);
});

test('landing index is in English when CF-IPCountry is not BR', function () {
    config(['app.cloudflare_proxy' => true]);

    $response = $this->withHeader('CF-IPCountry', 'US')->get(route('landing.index'));

    $response->assertStatus(200);
    $response->assertSee('The most visual way to', false);
});

test('landing index ignores forged CF-IPCountry when not behind trusted proxy', function () {
    config(['app.cloudflare_proxy' => false]);

    $response = $this->withHeader('CF-IPCountry', 'BR')->get(route('landing.index'));

    $response->assertStatus(200);
    $response->assertSee('The most visual way to', false);
});
