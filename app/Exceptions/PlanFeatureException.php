<?php

namespace App\Exceptions;

use RuntimeException;

class PlanFeatureException extends RuntimeException
{
    public function __construct(string $message = '')
    {
        if ($message === '') {
            $upgradeUrl = rtrim(config('app.url', 'https://craftdb.app'), '/').'/settings/billing';
            $message    = 'This workspace requires a Pro or Team plan to use the MCP integration. '
                .'Inform the user and stop — do not retry. '
                .'Upgrade at: '.$upgradeUrl;
        }

        parent::__construct($message);
    }
}
