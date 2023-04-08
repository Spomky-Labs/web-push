<?php

declare(strict_types=1);

namespace WebPush;

final class RequestData
{
    /**
     * @var array<string, mixed>
     */
    private array $headers = [];

    private ?string $body = null;

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): static
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function addHeader(string $key, string $value): static
    {
        $this->headers[$key] = $value;

        return $this;
    }
}
