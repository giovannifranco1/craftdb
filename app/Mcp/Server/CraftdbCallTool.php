<?php

declare(strict_types=1);

namespace App\Mcp\Server;

use App\Exceptions\DiagramLimitExceededException;
use App\Exceptions\PlanFeatureException;
use Generator;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Exceptions\JsonRpcException;
use Laravel\Mcp\Server\Methods\CallTool;
use Laravel\Mcp\Server\ServerContext;
use Laravel\Mcp\Server\Transport\JsonRpcRequest;
use Laravel\Mcp\Server\Transport\JsonRpcResponse;
use Laravel\Mcp\Support\ValidationMessages;

/**
 * Extends default MCP tool invocation to map domain and auth errors to tool errors (JSON-RPC),
 * instead of a generic MCP server failure.
 */
class CraftdbCallTool extends CallTool
{
    /**
     * @return JsonRpcResponse|Generator<JsonRpcResponse>
     *
     * @throws JsonRpcException
     */
    public function handle(JsonRpcRequest $request, ServerContext $context): Generator|JsonRpcResponse
    {
        if (is_null($request->get('name'))) {
            throw new JsonRpcException(
                'Missing [name] parameter.',
                -32602,
                $request->id,
            );
        }

        $tool = $context
            ->tools()
            ->first(
                fn ($tool): bool => $tool->name() === $request->params['name'],
                fn () => throw new JsonRpcException(
                    "Tool [{$request->params['name']}] not found.",
                    -32602,
                    $request->id,
                ));

        try {
            // @phpstan-ignore-next-line
            $response = Container::getInstance()->call([$tool, 'handle']);
        } catch (ValidationException $validationException) {
            $response = Response::error(ValidationMessages::from($validationException));
        } catch (AuthorizationException $authorizationException) {
            $message = $authorizationException->getMessage();
            $response = Response::error($message !== '' ? $message : __('This action is unauthorized.'));
        } catch (PlanFeatureException|DiagramLimitExceededException $e) {
            $response = Response::error($e->getMessage());
        } catch (ModelNotFoundException) {
            $response = Response::error(__('The requested resource was not found.'));
        }

        return is_iterable($response)
            ? $this->toJsonRpcStreamedResponse($request, $response, $this->serializable($tool))
            : $this->toJsonRpcResponse($request, $response, $this->serializable($tool));
    }
}
