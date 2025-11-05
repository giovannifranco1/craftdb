<?php

namespace Tests\Feature\Livewire\Diagram;

use App\Livewire\Diagram\TableList;
use Livewire\Livewire;
use Tests\TestCase;

class TableListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(TableList::class)
            ->assertStatus(200);
    }
}
