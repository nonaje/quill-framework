<?php

namespace App\Http\Middlewares;

use App\Core\Request\Request;
use App\Core\Response\Response;

final class Authenticate
{
    public function __invoke(Request $request, Response $response): void
    {
        dd(__METHOD__);
    }
}