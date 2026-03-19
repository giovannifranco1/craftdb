<?php

namespace App\Livewire\Data;

use App\Models\Column;
use Livewire\Wireable;

class ColumnData implements Wireable
{
    public function __construct(
        public int $id,
        public string $name,
        public string $type,
        public bool $is_primary,
        public bool $is_foreign,
        public bool $is_notnull,
        public int $order = 0,
        public ?int $length = null,
        public ?bool $relationships_from_exists = null,
        public ?bool $relationships_to_exists = null,
        public ?string $default_value = null,
        public ?string $comment = null,
        public bool $__new = false,
    ) {}

    /**
     * Check if this is a primary key
     */
    public function isPrimary(): bool
    {
        return $this->is_primary;
    }

    /**
     * Check if this is a foreign key
     */
    public function isForeign(): bool
    {
        return $this->is_foreign;
    }

    /**
     * Check if this column is nullable
     */
    public function isNullable(): bool
    {
        return !$this->is_notnull;
    }

    /**
     * Convert to array for Livewire serialization
     */
    /** @return array<string, mixed> */
    public function toLivewire(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'is_primary' => $this->is_primary,
            'is_foreign' => $this->is_foreign,
            'is_notnull' => $this->is_notnull,
            'order' => $this->order,
            'length' => $this->length,
            'default_value' => $this->default_value,
            'relationships_from_exists' => $this->relationships_from_exists,
            'relationships_to_exists' => $this->relationships_to_exists,
            'comment' => $this->comment,
        ];
    }

    /**
     * Reconstruct from Livewire serialized data
     */
    public static function fromLivewire(mixed $value): self
    {
        return new self(
            id: $value['id'],
            name: $value['name'],
            type: $value['type'],
            is_primary: $value['is_primary'],
            is_foreign: $value['is_foreign'],
            is_notnull: $value['is_notnull'],
            order: $value['order'] ?? 0,
            length: $value['length'] ?? null,
            relationships_from_exists: data_get($value, 'relationships_from_exists', null),
            relationships_to_exists: data_get($value, 'relationships_to_exists', null),
            default_value: data_get($value, 'default_value', null),
            comment: data_get($value, 'comment', null),
        );
    }

    /**
     * Create from Eloquent model
     */
    public static function fromModel(Column $column): self
    {
        return new self(
            id: $column->id,
            name: $column->name,
            type: $column->type,
            is_primary: (bool) $column->is_primary,
            is_foreign: (bool) $column->is_foreign,
            is_notnull: (bool) $column->is_notnull,
            order: $column->order ?? 0,
            length: $column->length ?? null,
            relationships_from_exists: $column->relationships_from_exists ?? null,
            relationships_to_exists: $column->relationships_to_exists ?? null,
            default_value: $column->default_value ?? null,
            comment: $column->comment ?? null,
        );
    }

    /**
     * Convert to array
     */
    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->toLivewire();
    }
}
