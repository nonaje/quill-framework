<?php

declare(strict_types=1);

namespace Tests\Unit\Response;

use PHPUnit\Framework\Attributes\Test;
use Quill\Enums\Http\HttpCode;
use Quill\Response\Response;
use Tests\TestCase;

final class ResponseTest extends TestCase
{
    #[Test]
    public function it_builds_json_plain_and_html_bodies_with_expected_headers(): void
    {
        $response = new Response(HttpCode::OK);

        $json = $response->json(['foo' => 'bar']);
        self::assertSame('application/json; charset=utf-8', $json->getHeaderLine('Content-Type'));
        $json->getBody()->rewind();
        self::assertSame('{"foo":"bar"}', $json->getBody()->getContents());

        $plain = $response->plain('hello');
        $plain->getBody()->rewind();
        self::assertSame('hello', $plain->getBody()->getContents());
        self::assertSame('text/plain; charset=utf-8', $plain->getHeaderLine('Content-Type'));

        $html = $response->html('<p>ok</p>');
        $html->getBody()->rewind();
        self::assertSame('<p>ok</p>', $html->getBody()->getContents());
        self::assertSame('text/html; charset=utf-8', $html->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function it_can_update_status_and_headers_fluently(): void
    {
        $response = (new Response(HttpCode::BAD_REQUEST))
            ->status(HttpCode::CREATED)
            ->headers([
                'X-Foo' => 'bar',
                'X-Bar' => ['a', 'b'],
            ]);

        self::assertSame(201, $response->getStatusCode());
        self::assertSame('bar', $response->getHeaderLine('X-Foo'));
        self::assertSame('a, b', $response->getHeaderLine('X-Bar'));
    }
}
