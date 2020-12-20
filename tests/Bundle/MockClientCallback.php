<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2020 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace WebPush\Tests\Bundle;

use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MockClientCallback
{
    private string $body = '';
    private array $info = [];

    public function __invoke(string $method, string $url, array $options = []): ResponseInterface
    {
        return new MockResponse($this->body, $this->info);
    }

    public function setResponse(string $body, array $info): void
    {
        $this->body = $body;
        $this->info = $info;
    }
}
