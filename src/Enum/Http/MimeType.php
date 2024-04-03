<?php

namespace Quill\Enum\Http;

use Quill\Enum\Steroids;

enum MimeType: string
{
    use Steroids;

    case JSON = 'application/json';
}