<?php

use App\Models\Diagram;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    /** @var \Tests\TestCase $this */
    $this->autenticated();
});

function supportLinks(): array
{
    return [
        'craftdb.support.repo_url' => 'https://example.test/repo',
        'craftdb.support.github_sponsors_url' => 'https://example.test/sponsors',
        'craftdb.support.kofi_url' => 'https://example.test/kofi',
    ];
}

function emptySupportConfig(): array
{
    return [
        'craftdb.support.repo_url' => null,
        'craftdb.support.github_sponsors_url' => null,
        'craftdb.support.kofi_url' => null,
        'craftdb.support.buymeacoffee_url' => null,
        'craftdb.support.pix_key' => null,
    ];
}

it('LP07: /support renders the support page', function () {
    $this->get('/support')->assertOk()->assertSee(__('Support the project'));
});

it('LP08: configured links appear opening in a new tab; unset ones do not', function () {
    config(supportLinks());
    $html = $this->get('/support')->assertOk()->getContent();

    foreach (['repo', 'sponsors', 'kofi'] as $slug) {
        expect($html)->toMatch('#<a\b[^>]*href="https://example\.test/'.$slug.'"[^>]*>#');
        preg_match('#<a\b[^>]*href="https://example\.test/'.$slug.'"[^>]*>#', $html, $m);
        expect($m[0])->toContain('target="_blank"')->toContain('rel="noopener noreferrer"');
    }

    config(emptySupportConfig());
    $html = $this->get('/support')->assertOk()->getContent();
    expect($html)->not->toContain('example.test');
});

it('LP08: ignores non-http(s) support urls', function () {
    config(array_merge(emptySupportConfig(), ['craftdb.support.repo_url' => 'javascript:alert(1)']));
    $this->get('/support')->assertOk()
        ->assertDontSee('javascript:alert', false)
        ->assertSee(__('More ways to support are coming soon.'));
});

it('LP09: pix key shows with copy button, hidden when null', function () {
    config(['craftdb.support.pix_key' => 'pix-key-123']);
    $this->get('/support')->assertOk()->assertSee('data-test="support-pix"', false)->assertSee('pix-key-123')->assertSee('navigator.clipboard', false);

    config(['craftdb.support.pix_key' => null]);
    $this->get('/support')->assertOk()->assertDontSee('navigator.clipboard', false)->assertDontSee('data-test="support-pix"', false);
});

it('LP10: nothing configured shows the coming soon notice', function () {
    config(emptySupportConfig());
    $this->get('/support')->assertOk()->assertSee(__('More ways to support are coming soon.'));
});

it('LP12: user menu and footer link to support, no contact modal', function () {
    $diagram = Diagram::factory()->create(['team_id' => $this->team->id]);
    $pages = [
        $this->get('/dashboard')->getContent(),
        $this->get(route('diagram.show', ['team_id' => $this->team->id, 'diagram' => $diagram->slug]))->getContent(),
    ];

    foreach ($pages as $html) {
        expect($html)->toContain('href="'.route('support').'"')
            ->not->toContain('contact-us');
    }

    expect($pages[0])->toMatch('#<a\b[^>]*href="'.preg_quote(route('support'), '#').'"[^>]*>\s*<span class="text-red-500">#');
});

it('LP05/LP06: feedback table and model are gone', function () {
    expect(Schema::hasTable('feedback'))->toBeFalse()
        ->and(class_exists(\App\Models\Feedback::class))->toBeFalse();
});
