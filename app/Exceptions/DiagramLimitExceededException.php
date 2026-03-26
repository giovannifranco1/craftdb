<?php

namespace App\Exceptions;

use Exception;

class DiagramLimitExceededException extends Exception
{
    public function __construct()
    {
        $upgradeUrl = rtrim(config('app.url', 'https://craftdb.app'), '/').'/settings/billing';

        parent::__construct(
            'Diagram limit reached for this workspace. '
            .'To create a new diagram, either delete an existing one or upgrade the plan. '
            .'Upgrade at: '.$upgradeUrl
        );
    }
}
