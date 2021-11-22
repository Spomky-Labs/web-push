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

    public function getToken(): string
    {
        return $this->token;
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
