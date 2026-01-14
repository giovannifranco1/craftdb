<?php

namespace App\Enums;

enum RelationshipType: string
{
    case ONE_TO_ONE = '1:1';
    case ONE_TO_MANY = '1:n';
    case MANY_TO_ONE = 'n:1';
    case MANY_TO_MANY = 'n:n';

    /**
     * Get all relationship type values as an array
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get a human-readable label for the relationship type
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::ONE_TO_ONE => 'One to One',
            self::ONE_TO_MANY => 'One to Many',
            self::MANY_TO_ONE => 'Many to One',
            self::MANY_TO_MANY => 'Many to Many',
        };
    }
}
