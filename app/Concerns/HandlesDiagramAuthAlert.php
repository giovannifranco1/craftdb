<?php

namespace App\Concerns;

use App\Exceptions\AuthorizationDiagramException;
use App\Models\Diagram;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * Trait HandlesDiagramAuthAlert
 *
 * This trait requires the following properties/methods to be present in the using class:
 * @property Diagram $diagram The diagram instance to authorize against
 *
 * @method void dispatch(string $event, mixed ...$params) Livewire dispatch method
 */
trait HandlesDiagramAuthAlert
{
    /**
     * Authorize the diagram action and throw exception if unauthorized.
     *
     * @param string $action The action to authorize (update|delete|view)
     * @param array<string> $dispatches The dispatches to dispatch if unauthorized
     * @return void
     */
    protected function authorizeDiagram(string $action, ?Diagram $diagram = null, array $dispatches = []): void
    {
        if (Gate::denies($action, $diagram ?? $this->diagram)) {
            foreach ($dispatches as $dispatch) {
                $this->dispatch($dispatch);
            }

            throw new AuthorizationDiagramException(__('You are not authorized to perform this action on this diagram.'));
        }
    }

    /**
     * Legacy method for backward compatibility.
     * @deprecated Use #[AuthorizeDiagram] attribute instead.
     */
    public function authorizeDiagramWithAlert(string $action, ?Diagram $diagram): bool
    {
        if (is_null($diagram)) {
            $this->dispatch('alert', message: __('Diagram not found.'), type: 'error');
            return false;
        }

        if (Gate::denies($action, $diagram)) {
            $this->dispatch('alert', message: __('You are not authorized to perform this action on this diagram.'), type: 'error');
            return false;
        }

        return true;
    }
}
