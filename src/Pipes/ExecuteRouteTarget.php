<?php

declare(strict_types=1);

namespace Quill\Pipes;

use Closure;
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Factory\QuillResponseFactory;

final class ExecuteRouteTarget
{
    public function __invoke(RequestInterface $request, Closure $next): ResponseInterface
    {
        $target = $request->getMatchedRoute()->target();
        $response = QuillResponseFactory::createQuillResponse();

        if (is_callable($target)) {
            return $target($request, $response);
        }

        $controller = $target[0];
        $method = $target[1] ?? '__invoke';

        return (new $controller($request, $response))->{$method}();
    }
}