<?php

declare(strict_types=1);

namespace Quill\Links;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Factory\QuillRequestFactory;
use Quill\Factory\QuillResponseFactory;
use LogicException;

final readonly class ExecuteRouteTarget implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): PsrResponseInterface
    {
        /** @var RouteInterface $matched */
        $matched = $request->getAttribute('route');
        $target = $matched->target();

        $response = QuillResponseFactory::createQuillResponse();
        $request = QuillRequestFactory::createFromPsrRequest($request)
            ->setMatchedRoute($matched);

        if (is_callable($target)) {
            /** @var ResponseInterface $final */
            $final = $target($request, $response);

            return $final->getPsrResponse();
        }

        if (is_array($target)) {
            $controller = $target[0];
            $method = $target[1] ?? '__invoke';

            /** @var ResponseInterface $final */
            $final = (new $controller($request, $response))->{$method}();

            return $final->getPsrResponse();
        }

        throw new LogicException('It is not possible to determine the target of the route');
    }
}