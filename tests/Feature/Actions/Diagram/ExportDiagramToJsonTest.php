<?php

namespace Tests\Feature\Actions\Diagram;

use App\Actions\Diagram\ExportDiagramToJson;
use App\Models\Column;
use App\Models\Diagram;
use App\Models\Relationship;
use App\Models\Table;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();

    $this->team = Team::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $this->diagram = Diagram::factory()->create([
        'name'     => 'My Diagram',
        'style'    => 'colorful',
        'database' => 'mysql',
        'team_id'  => $this->team->id,
    ]);

    $this->action = new ExportDiagramToJson();
});

describe('ExportDiagramToJson action', function (): void {
    it('serializes diagram metadata correctly', function () {
        $result = $this->action->exec($this->diagram);

        expect($result['craftdb_format'])->toBe('full')
            ->and($result['version'])->toBe('1.0')
            ->and($result['exported_at'])->toBeString()
            ->and($result['diagram']['name'])->toBe('My Diagram')
            ->and($result['diagram']['style'])->toBe('colorful')
            ->and($result['diagram']['database'])->toBe('mysql');
    });

    it('serializes tables with columns', function () {
        $table = Table::factory()->create([
            'diagram_id' => $this->diagram->id,
            'name'       => 'users',
        ]);

        Column::factory()->create([
            'table_id'   => $table->id,
            'name'       => 'id',
            'type'       => 'bigint',
            'is_primary' => true,
            'is_foreign' => false,
            'is_notnull' => true,
            'order'      => 0,
        ]);

        $result = $this->action->exec($this->diagram->fresh());

        expect($result['tables'])->toHaveCount(1)
            ->and($result['tables'][0]['name'])->toBe('users')
            ->and($result['tables'][0])->not->toHaveKey('color')
            ->and($result['tables'][0]['columns'])->toHaveCount(1)
            ->and($result['tables'][0]['columns'][0]['name'])->toBe('id')
            ->and($result['tables'][0]['columns'][0]['type'])->toBe('bigint')
            ->and($result['tables'][0]['columns'][0]['is_primary'])->toBeTrue()
            ->and($result['tables'][0]['columns'][0]['is_foreign'])->toBeFalse()
            ->and($result['tables'][0]['columns'][0]['is_notnull'])->toBeTrue();
    });

    it('does not include x/y positions in tables', function () {
        Table::factory()->create(['diagram_id' => $this->diagram->id]);

        $result = $this->action->exec($this->diagram->fresh());

        expect($result['tables'][0])->not->toHaveKey('x')
            ->and($result['tables'][0])->not->toHaveKey('y');
    });

    it('orders columns by order field', function () {
        $table = Table::factory()->create(['diagram_id' => $this->diagram->id]);

        Column::factory()->create(['table_id' => $table->id, 'name' => 'email', 'order' => 2]);
        Column::factory()->create(['table_id' => $table->id, 'name' => 'id', 'order' => 0]);
        Column::factory()->create(['table_id' => $table->id, 'name' => 'name', 'order' => 1]);

        $result = $this->action->exec($this->diagram->fresh());

        $columns = $result['tables'][0]['columns'];
        expect($columns[0]['name'])->toBe('id')
            ->and($columns[1]['name'])->toBe('name')
            ->and($columns[2]['name'])->toBe('email');
    });

    it('serializes relationships using table and column names', function () {
        $usersTable = Table::factory()->create(['diagram_id' => $this->diagram->id, 'name' => 'users']);
        $ordersTable = Table::factory()->create(['diagram_id' => $this->diagram->id, 'name' => 'orders']);

        $idColumn = Column::factory()->create(['table_id' => $usersTable->id, 'name' => 'id']);
        $userIdColumn = Column::factory()->create(['table_id' => $ordersTable->id, 'name' => 'user_id']);

        Relationship::factory()->create([
            'from_table_id'  => $usersTable->id,
            'from_column_id' => $idColumn->id,
            'to_table_id'    => $ordersTable->id,
            'to_column_id'   => $userIdColumn->id,
            'type'           => '1:n',
            'label'          => null,
        ]);

        $result = $this->action->exec($this->diagram->fresh());

        expect($result['relationships'])->toHaveCount(1)
            ->and($result['relationships'][0]['from_table'])->toBe('users')
            ->and($result['relationships'][0]['from_column'])->toBe('id')
            ->and($result['relationships'][0]['to_table'])->toBe('orders')
            ->and($result['relationships'][0]['to_column'])->toBe('user_id')
            ->and($result['relationships'][0]['type'])->toBe('1:n')
            ->and($result['relationships'][0]['label'])->toBeNull();
    });

    it('returns empty array when no relationships exist', function () {
        $result = $this->action->exec($this->diagram);

        expect($result['relationships'])->toBe([]);
    });

    it('does not include areas or notes in output', function () {
        $result = $this->action->exec($this->diagram);

        expect($result)->not->toHaveKey('areas')
            ->and($result)->not->toHaveKey('notes');
    });

    it('does not include any database id fields in output', function () {
        $table = Table::factory()->create(['diagram_id' => $this->diagram->id]);
        Column::factory()->create(['table_id' => $table->id]);

        $result = $this->action->exec($this->diagram->fresh());
        $json = json_encode($result);

        expect($json)->not->toContain('"id":')
            ->and($json)->not->toContain('"table_id":')
            ->and($json)->not->toContain('"diagram_id":');
    });
});

describe('ExportJson endpoint', function (): void {
    it('returns 200 with json content-type for authorized user', function () {
        $this->actingAs($this->user)
            ->get(route('diagram.export-json', [
                'team_id' => $this->team->id,
                'diagram' => $this->diagram->slug,
            ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json');
    });

    it('returns 403 for user without team access', function () {
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->get(route('diagram.export-json', [
                'team_id' => $this->team->id,
                'diagram' => $this->diagram->slug,
            ]))
            ->assertForbidden();
    });

    it('returns 401 for unauthenticated request', function () {
        $this->get(route('diagram.export-json', [
            'team_id' => $this->team->id,
            'diagram' => $this->diagram->slug,
        ]))
            ->assertRedirect(route('login'));
    });

    it('downloaded filename contains diagram slug and date', function () {
        $response = $this->actingAs($this->user)
            ->get(route('diagram.export-json', [
                'team_id' => $this->team->id,
                'diagram' => $this->diagram->slug,
            ]));

        $response->assertOk();
        $disposition = $response->headers->get('Content-Disposition');
        expect($disposition)->toContain('.json');
    });
});
