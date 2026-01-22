<?php

namespace App\Exceptions;

use Exception;

class TableColumnLimitExceededException extends Exception
{
    public int $totalColumns;

    /**
     * Create a new exception instance.
     *
     * @param int $totalColumns
     */
    public function __construct(int $totalColumns)
    {
        $this->totalColumns = $totalColumns;

        $message = "Maximum number of columns reached. Total: {$totalColumns}";

        parent::__construct($message);
    }
}
