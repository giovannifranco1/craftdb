<?php

namespace App\Concerns;

use App\Models\Diagram;
use Illuminate\Support\Facades\Gate;

trait HandlesDiagramAuthorizationWithAlertError
{
    public function authorizeDiagramWithAlert(string $action, Diagram $diagram): bool
    {
        if (Gate::denies($action, $diagram)) {
            $this->dispatch('alert', message: __('You are not authorized to perform this action on this diagram.'), type: 'error');
            return false;
        }

        return true;
    }
}
