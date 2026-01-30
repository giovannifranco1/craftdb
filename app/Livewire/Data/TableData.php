<?php

namespace App\Livewire\Data;

use App\Models\Column;
use App\Models\Table;
use Livewire\Wireable;

class TableData implements Wireable
{
    public function __construct(
        public int $id,
        public string $name,
        public string $color,
        public int $x,
        public int $y,
        public int $columns_count,
        public bool $columns_exists,
        public int $primary_count,
        public int $foreign_count,
        public int $resize_column_position,
        /** @var array<ColumnData> */
        public array $columns = [],
    ) {}

    /**
     * Check if the table has columns
     */
    public function hasColumns(): bool
    {
        return $this->columns_count > 0;
    }

    /**
     * Get primary columns
     */
    public function getPrimaryColumns(): array
    {
        return array_filter($this->columns, fn($col) => $col['is_primary'] ?? false);
    }

    /**
     * Get foreign columns
     */
    public function getForeignColumns(): array
    {
        return array_filter($this->columns, fn($col) => $col['is_foreign'] ?? false);
    }

    /**
     * Get the color or default
     */
    public function getColor(): string
    {
        return $this->color ?? \App\Models\Table::COLOR_DEFAULT;
    }

    /**
     * Convert to array for Livewire serialization
     */
    public function toLivewire(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'x' => $this->x,
            'y' => $this->y,
            'columns_count' => $this->columns_count,
            'columns_exists' => $this->columns_exists,
            'primary_count' => $this->primary_count,
            'foreign_count' => $this->foreign_count,
            'resize_column_position' => $this->resize_column_position,
            'columns' => $this->columns,
        ];
    }

    /**
     * Reconstruct from Livewire serialized data
     */
    public static function fromLivewire($value): self
    {
        return new self(
            id: $value['id'],
            name: $value['name'],
            color: $value['color'],
            x: $value['x'],
            y: $value['y'],
            columns_count: $value['columns_count'],
            columns_exists: $value['columns_exists'],
            primary_count: $value['primary_count'],
            foreign_count: $value['foreign_count'],
            resize_column_position: $value['resize_column_position'],
            columns: $value['columns'] ?? [],
        );
    }

    /**
     * Create from Eloquent model
     */
    public static function fromModel(Table $table): self
    {
        return new self(
            id: $table->id,
            name: $table->name,
            color: $table->color ?? \App\Models\Table::COLOR_DEFAULT,
            x: $table->x,
            y: $table->y,
            columns_count: $table->columns_count ?? 0,
            columns_exists: $table->columns_exists ?? false,
            primary_count: $table->primary_count ?? 0,
            foreign_count: $table->foreign_count ?? 0,
            resize_column_position: $table->resize_column_position ?? 10,

            /** @var array<ColumnData> */
            columns: $table->columns?->map(fn(Column $col): ColumnData => ColumnData::fromModel($col))->all() ?? [],
        );
    }
}
