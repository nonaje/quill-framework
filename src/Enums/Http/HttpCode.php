<?php

declare(strict_types=1);

namespace Quill\Enums\Http;

use Quill\Enums\Steroids;

enum HttpCode: int
{
    use Steroids;

    // 1xx Informational
    case CONTINUE = 100;
    case SWITCHING_PROTOCOLS = 101;
    case PROCESSING = 102; // WebDAV
    case EARLY_HINTS = 103; // RFC 8297

    // 2xx Success
    case OK = 200;
    case CREATED = 201;
    case ACCEPTED = 202;
    case NON_AUTHORITATIVE_INFORMATION = 203;
    case NO_CONTENT = 204;
    case RESET_CONTENT = 205;
    case PARTIAL_CONTENT = 206;
    case MULTI_STATUS = 207; // WebDAV
    case ALREADY_REPORTED = 208; // WebDAV
    case IM_USED = 226; // RFC 3229

    // 3xx Redirection
    case MULTIPLE_CHOICES = 300;
    case MOVED_PERMANENTLY = 301;
    case FOUND = 302;
    case SEE_OTHER = 303;
    case NOT_MODIFIED = 304;
    case USE_PROXY = 305;
    case SWITCH_PROXY = 306;
    case TEMPORARY_REDIRECT = 307;
    case PERMANENT_REDIRECT = 308;

    // 4xx
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case PAYMENT_REQUIRED = 402;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    case METHOD_NOT_ALLOWED = 405;
    case NOT_ACCEPTABLE = 406;
    case PROXY_AUTHENTICATION_REQUIRED = 407;
    case REQUEST_TIMEOUT = 408;
    case CONFLICT = 409;
    case GONE = 410;
    case LENGTH_REQUIRED = 411;
    case PRECONDITION_FAILED = 412;
    case PAYLOAD_TOO_LARGE = 413;
    case URI_TOO_LONG = 414;
    case UNSUPPORTED_MEDIA_TYPE = 415;
    case RANGE_NOT_SATISFIABLE = 416;
    case EXPECTATION_FAILED = 417;
    case IM_A_TEAPOT = 418;
    case MISDIRECTED_REQUEST = 421;
    case UNPROCESSABLE_ENTITY = 422;
    case LOCKED = 423;
    case FAILED_DEPENDENCY = 424;
    case TOO_EARLY = 425;
    case UPGRADE_REQUIRED = 426;
    case PRECONDITION_REQUIRED = 428;
    case TOO_MANY_REQUESTS = 429;
    case REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    case UNAVAILABLE_FOR_LEGAL_REASONS = 451;

    // 5xx Server Error
    case INTERNAL_SERVER_ERROR = 500;
    case NOT_IMPLEMENTED = 501;
    case BAD_GATEWAY = 502;
    case SERVICE_UNAVAILABLE = 503;
    case GATEWAY_TIMEOUT = 504;
    case HTTP_VERSION_NOT_SUPPORTED = 505;
    case VARIANT_ALSO_NEGOTIATES = 506; // RFC 2295
    case INSUFFICIENT_STORAGE = 507; // WebDAV
    case LOOP_DETECTED = 508; // WebDAV
    case NOT_EXTENDED = 510; // RFC 2774
    case NETWORK_AUTHENTICATION_REQUIRED = 511; // RFC 6585
    
    // Unofficial codes
    case THIS_IS_FINE = 218; // Unofficial, Apache Web Server
    case PAGE_EXPIRED = 419; // Unofficial, Laravel Framework
    case ENHANCE_YOUR_CALM = 420; // Unofficial, Twitter
    case REQUEST_HEADER_TOO_LARGE = 430; // Unofficial, Shopify
    case BLOCKED_BY_WINDOWS_PARENTAL_CONTROLS = 450; // Unofficial, Microsoft
    case INVALID_TOKEN = 498; // Unofficial, Esri
    case TOKEN_REQUIRED = 499; // Unofficial, Esri
    case BANDWIDTH_LIMIT_EXCEEDED = 509; // Unofficial, Apache Web Server
    case SITE_IS_FROZEN = 530; // Unofficial, Pantheon web platform
    case NETWORK_READ_TIMEOUT = 598; // Unofficial, Some HTTP proxies
    case NETWORK_CONNECT_TIMEOUT = 599; // Unofficial, Some HTTP proxies
}
