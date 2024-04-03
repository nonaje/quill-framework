<?php

declare(strict_types=1);

namespace Quill\Factory\Psr7;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Quill\Contracts\Response\ResponseInterface;

class Psr7Factory
{
    private static null|ServerRequestFactoryInterface $serverRequestFactory = null;

    private static null|ResponseFactoryInterface $responseFactory = null;

    private static null|UriFactoryInterface $uriFactory = null;

    private static null|UploadedFileFactoryInterface $uploadedFileFactory = null;

    private static null|StreamFactoryInterface $streamFactory = null;

    public static function withServerRequestFactory(ServerRequestFactoryInterface $factory): void
    {
        self::$serverRequestFactory = $factory;
    }

    public static function withResponseFactory(ResponseFactoryInterface $factory): void
    {
        self::$responseFactory = $factory;
    }

    public static function withUriFactory(UriFactoryInterface $factory): void
    {
        self::$uriFactory = $factory;
    }

    public static function withUploadedFileFactory(UploadedFileFactoryInterface $factory): void
    {
        self::$uploadedFileFactory = $factory;
    }

    public static function withStreamFactory(StreamFactoryInterface $factory): void
    {
        self::$streamFactory = $factory;
    }

    public static function serverRequestFactory(): ServerRequestFactoryInterface
    {
        return self::$serverRequestFactory ?? new Psr17Factory;
    }

    public static function uriFactory(): UriFactoryInterface
    {
        return self::$uriFactory ?? new Psr17Factory;
    }

    public static function uploadedFileFactory(): UploadedFileFactoryInterface
    {
        return self::$uploadedFileFactory ?? new Psr17Factory;
    }

    public static function streamFactory(): StreamFactoryInterface
    {
        return self::$streamFactory ?? new Psr17Factory;
    }

    public static function responseFactory(): ResponseFactoryInterface
    {
        return self::$responseFactory ?? new Psr17Factory;
    }

    public static function createPsr7ServerRequest(): ServerRequestInterface
    {
        return (new ServerRequestCreator(
            serverRequestFactory: self::serverRequestFactory(),
            uriFactory: self::uriFactory(),
            uploadedFileFactory: self::uploadedFileFactory(),
            streamFactory: self::streamFactory()
        ))->fromGlobals();
    }
}
