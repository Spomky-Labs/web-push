<?php

declare(strict_types=1);

namespace WebPush\VAPID;

final class Header
{
    public function __construct(
        private readonly string $token,
        private readonly string $key
    ) {
    }

    public static function create(string $token, string $key): self
    {
        return new self($token, $key);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
