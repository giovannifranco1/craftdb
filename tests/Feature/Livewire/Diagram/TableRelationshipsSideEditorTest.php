<?php

namespace Tests\Feature\Livewire\Diagram;

use App\Livewire\Diagram\TableRelationshipsSideEditor;
use Livewire\Livewire;
use Tests\TestCase;

class TableRelationshipsSideEditorTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(TableRelationshipsSideEditor::class)
            ->assertStatus(200);
    }
}
