<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Request\Request;
use App\Core\Response\Response;

abstract class Controller
{
    public function __construct(
        protected readonly Request $request,
        protected readonly Response $response
    ) { }
}
