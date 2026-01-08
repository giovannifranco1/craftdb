<?php

namespace App\Actions\Diagram;

use App\Models\Column;
use App\Models\Diagram;
use Illuminate\Support\Facades\DB;

class DeleteDiagram
{
    /**
     * @param Diagram $diagram
     * @return bool
     */
    public function exec(Diagram $diagram): bool
    {
        return DB::transaction(function () use ($diagram): bool {
            $diagram->relationships()->delete();
            $tableIds = $diagram->tables()->pluck('id');
            Column::whereIn('table_id', $tableIds)->delete();
            $diagram->tables()->delete();
            $diagram->areas()->delete();
            $diagram->notes()->delete();
            return $diagram->delete();
        });
    }
}
