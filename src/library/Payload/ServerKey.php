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

namespace WebPush\Payload;

use Assert\Assertion;

class ServerKey
{
    private const PUBLIC_KEY_SIZE = 65;
    private const PRIVATE_KEY_SIZE = 32;

    private string $publicKey;
    private string $privateKey;

    public function __construct(string $publicKey, string $privateKey)
    {
        Assertion::length($publicKey, self::PUBLIC_KEY_SIZE, 'Invalid public key length', null, '8bit');
        Assertion::length($privateKey, self::PRIVATE_KEY_SIZE, 'Invalid private key length', null, '8bit');
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
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
