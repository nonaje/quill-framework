<?php

declare(strict_types=1);

return new class implements \Quill\Contracts\MiddlewareInterface {
    public function handle(\Quill\Request\Request $request, \Quill\Response\Response $response): void
    {
        dump(__METHOD__);
    }
};