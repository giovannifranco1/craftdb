<?php

namespace App\Actions\Diagram;

use App\Models\Table;

class UpdateResizePositionTable
{
    public function __construct(
        public UpdateTable $updateTableAction
    ) {}

    public function exec(Table $table): Table
    {
        $maxColumns = $table->columns()->count();

        $newResizeColumnPosition = $table->resize_column_position;

        if($newResizeColumnPosition == $maxColumns) {
            $newResizeColumnPosition = $newResizeColumnPosition + 1;
        }

        $table->update(['resize_column_position' => $newResizeColumnPosition]);

        return $table;
    }
}
