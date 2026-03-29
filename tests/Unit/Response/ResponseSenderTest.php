<?php

declare(strict_types=1);

namespace Tests\Unit\Response;

use PHPUnit\Framework\Attributes\Test;
use Quill\Enums\Http\HttpCode;
use Quill\Response\Response;
use Quill\Response\ResponseSender;
use Tests\TestCase;

final class ResponseSenderTest extends TestCase
{
    #[Test]
    public function it_emits_status_headers_and_body(): void
    {
        $capturedHeaders = [];
        $capturedBody = null;

        $sender = new ResponseSender(
            headerEmitter: function (string $header, bool $replace = true, ?int $code = null) use (&$capturedHeaders): void {
                $capturedHeaders[] = ['header' => $header, 'replace' => $replace, 'code' => $code];
            },
            bodyEmitter: function (string $body) use (&$capturedBody): void {
                $capturedBody = $body;
            },
            terminator: static function (): void {
                // avoid terminating the test process
            }
        );

        $response = (new Response(HttpCode::OK))
            ->plain('Hello world')
            ->withHeader('X-Trace', 'abc123');

        $sender->send($response);

        self::assertSame('Hello world', $capturedBody);
        self::assertSame('HTTP/1.1 200 OK', $capturedHeaders[0]['header']);
        self::assertSame('Content-Type: text/plain; charset=utf-8', $capturedHeaders[1]['header']);
        self::assertSame('X-Trace: abc123', $capturedHeaders[2]['header']);
    }

    #[Test]
    public function it_appends_default_content_type_when_missing(): void
    {
        $capturedHeaders = [];

        $sender = new ResponseSender(
            headerEmitter: function (string $header, bool $replace = true, ?int $code = null) use (&$capturedHeaders): void {
                $capturedHeaders[] = ['header' => $header, 'replace' => $replace, 'code' => $code];
            },
            bodyEmitter: static function (string $body): void {
                // no output required for this assertion
            },
            terminator: static function (): void {
            }
        );

        $sender->send(new Response(HttpCode::NO_CONTENT));

        $contentTypeHeaders = array_values(array_filter(
            $capturedHeaders,
            static fn (array $header) => str_starts_with($header['header'], 'Content-Type: ')
        ));

        self::assertSame('Content-Type: application/json; charset=utf-8', $contentTypeHeaders[0]['header']);
    }
}
