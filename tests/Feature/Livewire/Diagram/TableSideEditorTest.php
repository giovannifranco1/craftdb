<?php

namespace Tests\Feature\Livewire\Diagram;

use App\Livewire\Diagram\TableSideEditor;
use Livewire\Livewire;
use Tests\TestCase;

class TableSideEditorTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(TableSideEditor::class)
            ->assertStatus(200);
    }
}
