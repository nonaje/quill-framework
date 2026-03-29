<?php

declare(strict_types=1);

namespace Quill\Factory\Psr7;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Quill\Response\Response;

class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $response = new Response();

        return $reasonPhrase === ''
            ? $response->withStatus($code)
            : $response->withStatus($code, $reasonPhrase);
    }
}
