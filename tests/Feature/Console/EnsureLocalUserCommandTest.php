<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

describe('craftdb:local-user', function (): void {
    it('DOCK07: prints url, email and password on first run', function () {
        /** @var TestCase $this */
        config([
            'app.url' => 'http://localhost:8080',
            'craftdb.local_user.email' => 'local@craftdb.local',
            'craftdb.local_user.password' => 'craftdb',
        ]);

        $this->artisan('craftdb:local-user')
            ->expectsOutputToContain('http://localhost:8080')
            ->expectsOutputToContain('local@craftdb.local')
            ->expectsOutputToContain('craftdb')
            ->assertSuccessful();

        expect(User::where('email', 'local@craftdb.local')->exists())->toBeTrue();
    });

    it('DOCK07: does not print the password when the user already exists', function () {
        /** @var TestCase $this */
        config([
            'craftdb.local_user.email' => 'local@craftdb.local',
            'craftdb.local_user.password' => 'senha-secreta-123',
        ]);

        $this->artisan('craftdb:local-user')->assertSuccessful();

        $this->artisan('craftdb:local-user')
            ->expectsOutputToContain('local@craftdb.local')
            ->doesntExpectOutputToContain('senha-secreta-123')
            ->assertSuccessful();
    });

    it('DOCK10: uses email and password from config', function () {
        /** @var TestCase $this */
        config([
            'craftdb.local_user.email' => 'dev@example.com',
            'craftdb.local_user.password' => 'segredo123',
        ]);

        $this->artisan('craftdb:local-user')->assertSuccessful();

        expect(User::where('email', 'dev@example.com')->exists())->toBeTrue();
    });
});
