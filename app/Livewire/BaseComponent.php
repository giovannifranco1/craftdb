<?php

namespace App\Livewire;

use App\Exceptions\AuthorizationDiagramException;
use App\Exceptions\DiagramLimitExceededException;
use Livewire\Component;
use Throwable;

abstract class BaseComponent extends Component
{
    public function exception(Throwable $e, $stopPropagation): void
    {
        if ($e instanceof AuthorizationDiagramException) {
            $stopPropagation();
            $this->alert(message: __('You are not authorized to perform this action on this diagram.'), type: 'error');
        }

        if ($e instanceof DiagramLimitExceededException) {
            $stopPropagation();
            $this->redirect(route('dashboard'), navigate: true);
            $this->alert(message: $e->getMessage(), type: 'error');
        }
    }

    public function alert(string $message, string $type = 'default', bool $topCenter = false): void
    {
        $this->dispatch('alert', message: $message, type: $type, topCenter: $topCenter);
    }
}
