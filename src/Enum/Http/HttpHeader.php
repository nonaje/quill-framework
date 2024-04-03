<?php

declare(strict_types=1);

namespace Quill\Enum\Http;

use Quill\Enum\Steroids;

enum HttpHeader: string
{
    use Steroids;

    case CONTENT_TYPE = 'Content-Type';
}
