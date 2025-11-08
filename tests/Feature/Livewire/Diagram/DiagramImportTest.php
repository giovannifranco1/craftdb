<?php

it('has a diagram create page', function () {
    $this->get(route('diagram.create'))
        ->assertOk()
        ->assertSee('Create Diagram');
});

it('import diagram page works', function () {
    $this->get(route('diagram.import'))
        ->assertOk()
        ->assertSee('Import Diagram');
});
