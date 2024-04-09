<?php

declare(strict_types=1);

namespace Quill\Enums\Http;

use Quill\Enums\Steroids;

enum HttpHeader: string
{
    use Steroids;

    case CONTENT_TYPE = 'Content-Type';
}
