<?php

test('login page is in Portuguese when cf-ipcountry is BR behind trusted proxy', function () {
    config(['app.cloudflare_proxy' => true]);

    $response = $this->withHeader('cf-ipcountry', 'BR')->get(route('login'));

    $response->assertStatus(200);
    $response->assertSee('Bem-vindo de volta', false);
});

test('register page is in Portuguese when cf-ipcountry is BR behind trusted proxy', function () {
    config(['app.cloudflare_proxy' => true]);

    $response = $this->withHeader('cf-ipcountry', 'BR')->get(route('register'));

    $response->assertStatus(200);
    $response->assertSee('Criar uma conta', false);
});

test('login page stays in English when cf-ipcountry is not BR', function () {
    config(['app.cloudflare_proxy' => true]);

    $response = $this->withHeader('cf-ipcountry', 'US')->get(route('login'));

    $response->assertStatus(200);
    $response->assertSee('Welcome back', false);
});

test('login page ignores forged cf-ipcountry when not behind trusted proxy', function () {
    config(['app.cloudflare_proxy' => false]);

    $response = $this->withHeader('cf-ipcountry', 'BR')->get(route('login'));

    $response->assertStatus(200);
    $response->assertSee('Welcome back', false);
});

test('landing index is in Portuguese when cf-ipcountry is BR behind trusted proxy', function () {
    config(['app.cloudflare_proxy' => true]);

    $response = $this->withHeader('cf-ipcountry', 'BR')->get(route('landing.index'));

    $response->assertStatus(200);
    $response->assertSee('A forma mais visual', false);
});

test('landing index is in English when cf-ipcountry is not BR', function () {
    config(['app.cloudflare_proxy' => true]);

    $response = $this->withHeader('cf-ipcountry', 'US')->get(route('landing.index'));

    $response->assertStatus(200);
    $response->assertSee('The most visual way to', false);
});

test('landing index ignores forged cf-ipcountry when not behind trusted proxy', function () {
    config(['app.cloudflare_proxy' => false]);

    $response = $this->withHeader('cf-ipcountry', 'BR')->get(route('landing.index'));

    $response->assertStatus(200);
    $response->assertSee('The most visual way to', false);
});
