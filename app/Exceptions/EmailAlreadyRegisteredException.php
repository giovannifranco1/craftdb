<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;

class EmailAlreadyRegisteredException extends Exception implements ShouldntReport
{
    /** @var int */
    protected $code = 422;

    public static function forProvider(): self
    {
        return new self(__('auth.email_already_registered'));
    }
}
