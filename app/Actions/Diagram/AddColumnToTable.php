<?php

namespace App\Actions\Diagram;

use App\Exceptions\TableColumnLimitExceededException;
use App\Models\Column;
use App\Models\Table;
use App\Support\Helpers;
use Illuminate\Support\Facades\DB;

class AddColumnToTable
{
    public function __construct(
        public UpdateResizePositionTable $updateResizePositionTableAction
    ) {}

    /**
     * Add a new column to a table
     *
     *
     * @example
     * ```php
     * (new AddColumnToTable())->exec(Table::find(1));
     * $action->exec($tableModel);
     */
    public function exec(Table $table): Column
    {
        return DB::transaction(function () use ($table): Column {
            $table->lockForUpdate();

            $currentCount = $table->columns()->count();

            if ($currentCount >= config('diagram.table_columns_limit')) {
                throw new TableColumnLimitExceededException($currentCount);
            }

            $maxOrder = $table->columns()->max('order');
            $newOrder = Helpers::calculateNextOrder($maxOrder);

            return tap($table->columns()->create([
                    'name'  => 'new_column',
                    'type'  => 'varchar',
                    'order' => $newOrder,
                    'is_notnull' => true,
                ]), fn (): Table => $this->updateResizePositionTableAction->exec($table));
        });
    }
}
