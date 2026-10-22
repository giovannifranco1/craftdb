<?php

namespace Tests\Feature\Workspaces;

use App\Models\Diagram;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var \Tests\TestCase $this */
    $this->autenticated();
});

function visibleText(string $html): string
{
    $html = preg_replace('#<(script|style|template)\b.*?</\1>#is', '', $html);

    return html_entity_decode(strip_tags($html));
}

it('WS10: visible UI text uses Workspace wording and never Team/Time/Equipe/Members', function (string $locale) {
    app()->setLocale($locale);
    // The factory default team name ("<user>'s Team") is user data, not UI wording.
    $this->team->update(['name' => 'Main']);
    $diagram = Diagram::factory()->create(['team_id' => $this->team->id, 'database' => 'mysql']);

    $urls = [
        '/dashboard',
        '/settings/workspaces',
        '/settings/profile',
        route('diagram.show', [$this->team->id, $diagram->slug]),
    ];

    foreach ($urls as $url) {
        $text = visibleText($this->get($url)->assertOk()->getContent());

        expect($text)->not->toMatch('/\b(Team|Teams|Equipe|Membros|Members)\b/', "{$url} ({$locale})");
        expect($text)->not->toMatch('/\bTime\b/', "{$url} ({$locale})");
    }
})->with(['en', 'pt_BR']);

it('WS10: empty dashboard asks to create a workspace', function () {
    $this->team->delete();
    session()->forget('current_team');

    $text = visibleText(\Livewire\Livewire::test('pages::dashboard')->html());

    expect($text)->toContain('You have no workspaces. Create one to get started.')
        ->not->toMatch('/\b(Team|invited)\b/');
});
