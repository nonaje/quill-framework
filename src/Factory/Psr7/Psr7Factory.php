<?php

namespace Quill\Factory\Psr7;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class Psr7Factory
{
    protected static null|ServerRequestFactoryInterface $serverRequestFactory = null;

    protected static null|UriFactoryInterface $uriFactory = null;

    protected static null|UploadedFileFactoryInterface $uploadedFileFactory = null;

    protected static null|StreamFactoryInterface $streamFactory = null;

    public static function withServerRequestFactory(ServerRequestFactoryInterface $factory): void
    {
        self::$serverRequestFactory = $factory;
    }

    public static function withUriFactoryFactory(UriFactoryInterface $factory): void
    {
        self::$uriFactory = $factory;
    }

    public static function withUploadedFileFactoryFactory(UploadedFileFactoryInterface $factory): void
    {
        self::$uploadedFileFactory = $factory;
    }

    public static function withStreamFactoryFactory(StreamFactoryInterface $factory): void
    {
        self::$streamFactory = $factory;
    }

    protected static function serverRequestFactory(): ServerRequestFactoryInterface
    {
        return self::$serverRequestFactory ?? new Psr17Factory;
    }

    protected static function uriFactoryFactory(): UriFactoryInterface
    {
        return self::$uriFactory ?? new Psr17Factory;
    }

    protected static function uploadedFileFactoryFactory(): UploadedFileFactoryInterface
    {
        return self::$uploadedFileFactory ?? new Psr17Factory;
    }

    protected static function streamFactoryFactory(): StreamFactoryInterface
    {
        return self::$streamFactory ?? new Psr17Factory;
    }
}
