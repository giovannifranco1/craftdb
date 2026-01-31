<?php

namespace App\Actions\Diagram;

use App\Models\Column;
use Illuminate\Support\Facades\DB;

class DeleteColumnToTable
{
    public function exec(Column $column): Column
    {
        return DB::transaction(function () use ($column): Column {
            $column->lockForUpdate();

            $column->relationshipsFrom()->delete();
            $column->relationshipsTo()->delete();
            $column->delete();

            return $column;
        });
    }
}
