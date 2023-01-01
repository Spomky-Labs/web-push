<?php

declare(strict_types=1);

namespace WebPush\Payload;

use Assert\Assertion;

final class ServerKey
{
    private const PUBLIC_KEY_SIZE = 65;

    private const PRIVATE_KEY_SIZE = 32;

    private readonly string $publicKey;

    private readonly string $privateKey;

    public function __construct(string $publicKey, string $privateKey)
    {
        Assertion::length($publicKey, self::PUBLIC_KEY_SIZE, 'Invalid public key length', null, '8bit');
        Assertion::length($privateKey, self::PRIVATE_KEY_SIZE, 'Invalid private key length', null, '8bit');
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    public static function create(string $publicKey, string $privateKey): self
    {
        return new self($publicKey, $privateKey);
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }
}
