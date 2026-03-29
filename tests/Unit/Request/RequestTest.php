<?php

declare(strict_types=1);

namespace Tests\Unit\Request;

use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\Attributes\Test;
use Quill\Enums\Http\HttpMethod;
use Quill\Enums\RequestAttribute;
use Quill\Request\Request;
use Quill\Router\Route;
use Tests\TestCase;

final class RequestTest extends TestCase
{
    #[Test]
    public function it_exposes_basic_request_information(): void
    {
        $psrRequest = (new ServerRequest('POST', 'https://example.com/path?foo=bar'))
            ->withQueryParams(['foo' => 'bar']);

        $request = new Request($psrRequest);

        self::assertSame(HttpMethod::POST, $request->method());
        self::assertSame('bar', $request->query('foo'));
        self::assertNull($request->query('missing'));
    }

    #[Test]
    public function it_reads_route_params_from_attribute(): void
    {
        $route = new Route(new Uri('/users/{id}'), HttpMethod::GET, static fn () => null, [], ['id' => '10']);

        $psrRequest = (new ServerRequest('GET', 'https://example.com/users/10'))
            ->withAttribute(RequestAttribute::ROUTE->value, $route);

        $request = new Request($psrRequest);

        self::assertSame('10', $request->route('id'));
        self::assertSame('fallback', $request->route('missing', 'fallback'));
    }

    #[Test]
    public function it_merges_query_and_body_payloads_when_fetching_all_inputs(): void
    {
        $psrRequest = (new ServerRequest('POST', 'https://example.com/users?search=emma', body: Stream::create('{"name":"Emma"}')))
            ->withQueryParams(['search' => 'emma']);

        $request = new Request($psrRequest);

        self::assertSame(
            ['search' => 'emma', 'name' => 'Emma'],
            $request->all()
        );
    }

    #[Test]
    public function it_allows_reading_json_body_multiple_times_without_consuming_the_stream(): void
    {
        $psrRequest = new ServerRequest('POST', 'https://example.com/users', body: Stream::create('{"name":"Milo"}'));
        $request = new Request($psrRequest);

        self::assertSame('Milo', $request->body('name'));
        self::assertSame('Milo', $request->body('name'));
        self::assertSame(['name' => 'Milo'], $request->body());
        $psrRequest->getBody()->rewind();
        self::assertSame('{"name":"Milo"}', $psrRequest->getBody()->getContents());
    }

    #[Test]
    public function it_prefers_parsed_body_when_available(): void
    {
        $psrRequest = (new ServerRequest('POST', 'https://example.com/users'))
            ->withParsedBody(['name' => 'Ava']);

        $request = new Request($psrRequest);

        self::assertSame('Ava', $request->body('name'));
        self::assertSame(['name' => 'Ava'], $request->body());
    }

    #[Test]
    public function it_exposes_the_underlying_psr_request_for_headers_and_raw_access(): void
    {
        $psrRequest = (new ServerRequest('GET', 'https://example.com/profile', body: Stream::create('plain-text')))
            ->withHeader('X-Trace', 'abc123');

        $request = new Request($psrRequest);

        self::assertSame($psrRequest, $request->getPsrRequest());
        self::assertSame('abc123', $request->getPsrRequest()->getHeaderLine('X-Trace'));
        self::assertSame([], $request->body());
        $request->getPsrRequest()->getBody()->rewind();
        self::assertSame('plain-text', $request->getPsrRequest()->getBody()->getContents());
    }
}
