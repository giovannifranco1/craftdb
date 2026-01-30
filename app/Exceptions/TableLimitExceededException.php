<?php

namespace App\Exceptions;

use Exception;

class TableLimitExceededException extends Exception
{
    public int $totalTables;

    /**
     * Create a new exception instance.
     *
     * @param int $totalTables
     * @param int $limit
     */
    public function __construct(int $totalTables, int $limit = 30)
    {
        $this->totalTables = $totalTables;

        $message = "A importação excede o limite de {$limit} tabelas. Total encontrado: {$totalTables}.";

        parent::__construct($message);
    }

    public function getTotalTables(): int
    {
        return $this->totalTables;
    }
}
