<?php

namespace App\Enums;

enum Role: string
{
    case EDITOR = "editor";
    case VIEWER = "viewer";

    public function key(): int
    {
        return match ($this) {
            self::EDITOR => 1,
            self::VIEWER => 2,
        };
    }

    public function name(): string
    {
        return match ($this) {
            self::EDITOR => __('Editor'),
            self::VIEWER => __('Viewer'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::EDITOR => __('Can create, edit and view diagrams'),
            self::VIEWER => __('Can only view diagrams'),
        };
    }

    public function exists(): bool
    {
        return in_array($this, [self::EDITOR, self::VIEWER]);
    }
}
