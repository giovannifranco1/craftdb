<?php

namespace App\Actions\Diagram;

use App\Models\Table;
use Illuminate\Support\Facades\DB;

class DeleteTable
{
    /**
     * Delete a table and all its relationships and columns
     *
     * @example
     * ```php
     * (new DeleteTable())->exec(Table::find(1));
     * $action->exec($tableModel);
     * ```
     */
    public function exec(Table $table): void
    {
        DB::transaction(function () use ($table): void {
            $relatedTableIds = $table->relationships()
            ->pluck('from_table_id')
            ->merge($table->relationships()->pluck('to_table_id'))
            ->unique()
            ->sort();

            Table::query()
                ->whereIn('id', $relatedTableIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $table->relationships()->delete();
            $table->columns()->delete();
            $table->delete();
        });
    }
}
