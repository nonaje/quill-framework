<?php

declare(strict_types=1);

namespace Quill\Enums\Http;

use Quill\Enums\Steroids;

enum MimeType: string
{
    use Steroids;

    // text
    case PLAIN_TEXT = 'text/plain';
    case HTML = 'text/html';

    // application
    case JSON = 'application/json';
    case X_WWW_FORM_URL_ENCODED = 'application/x-www-form-urlencoded';

    // multipart
    case MULTIPART_FORM_DATA = 'multipart/form-data';
}
