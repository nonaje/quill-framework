<?php

declare(strict_types=1);

namespace Quill\Enums;

enum RequestAttribute: string
{
    case ERROR = 'ERROR';

    case ROUTE = 'ROUTE';
}
