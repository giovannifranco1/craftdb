<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

describe('craftdb:local-user', function (): void {
    it('NOAUTH13: prints url and email, never a password line', function () {
        /** @var TestCase $this */
        config([
            'app.url' => 'http://localhost:8080',
            'craftdb.local_user.email' => 'local@craftdb.local',
        ]);

        $this->artisan('craftdb:local-user')
            ->expectsOutputToContain('http://localhost:8080')
            ->expectsOutputToContain('local@craftdb.local')
            ->doesntExpectOutputToContain('Senha')
            ->assertSuccessful();

        expect(User::where('email', 'local@craftdb.local')->exists())->toBeTrue();
    });

    it('DOCK10: uses email from config', function () {
        /** @var TestCase $this */
        config([
            'craftdb.local_user.email' => 'dev@example.com',
        ]);

        $this->artisan('craftdb:local-user')->assertSuccessful();

        expect(User::where('email', 'dev@example.com')->exists())->toBeTrue();
    });
});
