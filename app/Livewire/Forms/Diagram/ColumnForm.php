<?php

namespace App\Livewire\Forms\Diagram;

use Livewire\Attributes\Validate;
use Livewire\Form;

class ColumnForm extends Form
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|max:50')]
    public string $type = 'VARCHAR';

    #[Validate('nullable|integer|min:0')]
    public ?int $length = null;

    #[Validate('boolean')]
    public bool $isPrimary = false;

    #[Validate('boolean')]
    public bool $isForeign = false;

    #[Validate('boolean')]
    public bool $isNullable = false;

    #[Validate('nullable|string|max:255')]
    public ?string $defaultValue = null;

    #[Validate('nullable|string')]
    public ?string $comment = null;
}

