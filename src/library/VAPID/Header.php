<?php

declare(strict_types=1);

namespace WebPush\VAPID;

class Header
{
    public function __construct(
        private string $token,
        private string $key
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
