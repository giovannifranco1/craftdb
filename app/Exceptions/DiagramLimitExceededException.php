<?php

namespace App\Exceptions;

use Exception;

class DiagramLimitExceededException extends Exception
{
    public function __construct()
    {
        parent::__construct(__('You have reached the maximum number of diagrams for your account.'));
    }
}
