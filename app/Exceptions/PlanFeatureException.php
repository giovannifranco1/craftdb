<?php

namespace App\Exceptions;

use RuntimeException;

class PlanFeatureException extends RuntimeException
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: __('This feature is not available on your current plan. Upgrade to unlock it.'));
    }
}
