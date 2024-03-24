<?php

declare(strict_types=1);

namespace App\Core\Request;

use App\Core\Patterns\Singleton;

class Request extends Singleton
{
    private const string GET = 'GET';
    private const string POST = 'POST';
    private const string PUT = 'PUT';
    private const string PATCH = 'PATCH';
    private const string DELETE = 'DELETE';

    protected function __construct(
        private array $rawRequest
    ) {
    }

    public function body(): array
    {
        return ['x' => 'x'];
    }

    public function method(): string
    {
        return self::{$this->rawRequest['REQUEST_METHOD']};
    }

    public function uri(): string
    {
        return $this->rawRequest['REQUEST_URI'];
    }
}
