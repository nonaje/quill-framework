<?php

declare(strict_types=1);

namespace Quill\Response;

use Exception;
use Nyholm\Psr7\Response as PsrResponse;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Enums\Http\HttpCode;
use Quill\Enums\Http\HttpHeader;
use Quill\Enums\Http\MimeType;

class Response extends PsrResponse implements ResponseInterface
{
    public function __construct(
        private HttpCode $status = HttpCode::INTERNAL_SERVER_ERROR,
        private array $headers = [],
        private ?StreamInterface $body = null,
        private string $version = '1.1',
        private string $reason = ''
    ) {
        parent::__construct(
            status: $this->status->value,
            headers: $this->headers,
            body: $this->body ?? Stream::create(''),
            version: $this->version,
            reason: $this->reason
        );
    }

    public function plain(string $plain): self
    {
        return $this->body($plain, MimeType::PLAIN_TEXT);
    }

    public function json(array $data): self
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $this->body($json ?: 'null', MimeType::JSON);
    }

    public function html(string $html): self
    {
        return $this->body($html, MimeType::HTML);
    }

    /**
     * @throws Exception
     */
    public function view(string $view): self
    {
        if (file_exists($path = $view)
            || file_exists($path = file_path($view))
            || file_exists($path = file_path($view . '.html'))
        ) {
            $html = file_get_contents($path);
            if ($html === false) {
                throw new Exception('The view "' . $view . '" is not readable.');
            }
            return $this->html($html);
        }

        throw new Exception('The view "' . $view . '" does not exist or is not readable.');
    }

    public function code(HttpCode $code): self
    {
        return $this->withStatus($code->value);
    }

    private function body(string $content, MimeType $mime): self
    {
        $stream = Stream::create($content);

        return $this
            ->withHeader(HttpHeader::CONTENT_TYPE->value, $mime->value)
            ->withBody($stream);
    }
}
