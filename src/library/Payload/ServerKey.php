<?php

declare(strict_types=1);

namespace WebPush\Payload;

use WebPush\Exception\OperationException;

final class ServerKey
{
    private const PUBLIC_KEY_SIZE = 65;

    private const PRIVATE_KEY_SIZE = 32;

    private readonly string $publicKey;

    private readonly string $privateKey;

    public function __construct(string $publicKey, string $privateKey)
    {
        mb_strlen($publicKey, '8bit') === self::PUBLIC_KEY_SIZE || throw new OperationException(
            'Invalid public key length'
        );
        mb_strlen($privateKey, '8bit') === self::PRIVATE_KEY_SIZE || throw new OperationException(
            'Invalid private key length'
        );
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
