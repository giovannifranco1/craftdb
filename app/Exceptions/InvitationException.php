<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;

/**
 * Expected invitation failures (expired, cancelled, already accepted).
 */
class InvitationException extends Exception implements ShouldntReport
{
    public static function alreadyAccepted(): self
    {
        return new self(__('Invitation already accepted.'));
    }

    public static function expired(): self
    {
        return new self(__('Invitation expired.'));
    }

    public static function cancelled(): self
    {
        return new self(__('Invitation cancelled.'));
    }
}
