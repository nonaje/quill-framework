<?php

declare(strict_types=1);

namespace Quill\Handler\Error;

use Quill\Contracts\Response\ResponseInterface;
use Quill\Enums\Http\HttpCode;

class JsonErrorHandler extends ErrorHandler
{
    /** @ineritDoc */
    protected function toResponse(): ResponseInterface
    {
        return $this->response
            ->code(HttpCode::tryFrom($this->error->getCode()) ?? HttpCode::SERVER_ERROR)
            ->json($this->buildBody());
    }

    private function buildBody(): array
    {
        $data = [
            'success' => false,
            'code' => $this->error->getCode() ?: HttpCode::SERVER_ERROR,
            'message' => $this->error->getMessage(),
        ];

        if ($this->displayErrors) {
            $data += [
                'file' => $this->error->getFile(),
                'line' => $this->error->getLine(),
                'trace' => $this->error->getTrace(),
            ];
        }

        return $data;
    }
}
