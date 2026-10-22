<?php

use App\Models\Diagram;

test('NOAUTH09: settings navigation has no password, 2FA or delete account entries', function () {
    /** @var TestCase $this */
    $this->autenticated();

    $this->get('/settings/profile')
        ->assertOk()
        ->assertSee('settings/appearance', false)
        ->assertSee('settings/language', false)
        ->assertSee('settings/integrations', false)
        ->assertSee('settings/profile', false)
        ->assertDontSee('settings/password', false)
        ->assertDontSee('settings/two-factor', false)
        ->assertDontSee('delete-user', false);
});

test('NOAUTH08: dashboard and diagram pages have no logout form', function () {
    /** @var TestCase $this */
    $this->autenticated();

    $diagram = Diagram::factory()->create(['team_id' => $this->team->id]);

    foreach (['/dashboard', route('diagram.show', ['team_id' => $this->team->id, 'diagram' => $diagram->slug])] as $url) {
        $this->get($url)
            ->assertOk()
            ->assertDontSee('/logout', false)
            ->assertDontSee('logout-button', false)
            ->assertDontSee('Log Out');
    }
});
