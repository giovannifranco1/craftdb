<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;

class AuthorizationDiagramException extends Exception implements ShouldntReport
{

    /** @var int */
    protected $code = 403;

    public static function withMessage(string $message): self
    {
        return new self($message);
    }

    /**
     * Report the exception.
     */
    protected static function summarize(mixed $validator): void
    {
        // do nothing
    }

}
