<?php

namespace App\Actions\Diagram;

use App\Models\Table;

class UpdateTable
{
    /**
     * Update a table
     *
     * @param Table $table
     * @param array<string, mixed> $data
     *
     * @example
     * ```php
     * (new UpdateTable())->exec(Table::find(1), ['name' => 'New Name']);
     * $action->exec($tableModel, [
     *     'name' => 'New Name',
     *     'color' => 'red',
     *     'x' => 100,
     *     'y' => 100,
     *     'resize_column_position' => 10,
     * ]);
     * ```
     */
    public function exec(Table $table, array $data): Table
    {
        $this->_validate($data);

        $table->update($data);

        return $table;
    }


    /** @param array<string, mixed> $data */
    private function _validate(array $data): void
    {
        validator($data, [
            'name' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:255'],
            'x' => ['nullable', 'integer'],
            'y' => ['nullable', 'integer'],
            'resize_column_position' => ['nullable', 'numeric', 'min:1'],
        ])->validate();
    }
}
