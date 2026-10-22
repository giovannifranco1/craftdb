<?php

use App\Models\Diagram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

test('FREE14 public diagram route no longer exists', function () {
    expect(Route::has('diagram.public'))->toBeFalse();
});

test('FREE15 diagram page keeps internal link and drops public sharing', function () {
    $this->autenticated();
    $diagram = Diagram::factory()->create(['team_id' => $this->team->id]);

    $this->get(route('diagram.show', ['team_id' => $this->team->id, 'diagram' => $diagram->slug]))
        ->assertOk()
        ->assertSee(__('Copy internal link'))
        ->assertDontSee('makePublic')
        ->assertDontSee('revokePublic')
        ->assertDontSee(__('Make Public'))
        ->assertDontSee(__('Revoke Public Access'));
});

test('FREE16 public sharing classes and methods are gone', function () {
    expect(method_exists(Diagram::class, 'makePublic'))->toBeFalse()
        ->and(method_exists(Diagram::class, 'getPublicUrl'))->toBeFalse()
        ->and(class_exists('App\View\Components\Layouts\CanvasPublic'))->toBeFalse()
        ->and(class_exists('App\Livewire\Contracts\PublicCanvas'))->toBeFalse();
});
