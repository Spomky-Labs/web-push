<?php

declare(strict_types=1);

namespace WebPush\Tests;

use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 */
final class MockClientCallback
{
    private string $body = '';

    private array $info = [];

    public function __invoke(): ResponseInterface
    {
        return new MockResponse($this->body, $this->info);
    }

    public function setResponse(string $body, array $info): void
    {
        $this->body = $body;
        $this->info = $info;
    }
}
