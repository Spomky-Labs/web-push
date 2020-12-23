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
use Psr\Http\Message\RequestInterface;
use function Safe\pack;
use function Safe\sprintf;
use WebPush\Base64Url;

final class AESGCM extends AbstractAESGCM
{
    private const ENCODING = 'aesgcm';
    private const PADDING_MAX = 4078;

    public static function create(): self
    {
        return new self();
    }

    public function customPadding(int $padding): self
    {
        Assertion::range($padding, self::PADDING_NONE, self::PADDING_MAX, 'Invalid padding size');
        $this->padding = $padding;

        return $this;
    }

    public function maxPadding(): self
    {
        $this->padding = self::PADDING_MAX;

        return $this;
    }

    public function name(): string
    {
        return self::ENCODING;
    }

    protected function getKeyInfo(string $userAgentPublicKey, ServerKey $serverKey): string
    {
        return "Content-Encoding: auth\0";
    }

    protected function getContext(string $userAgentPublicKey, ServerKey $serverKey): string
    {
        return sprintf('%s%s%s%s',
            "P-256\0\0A",
            $userAgentPublicKey,
            "\0A",
            $serverKey->getPublicKey()
        );
    }

    protected function addPadding(string $payload): string
    {
        $payloadLength = mb_strlen($payload, '8bit');
        $paddingLength = max(self::PADDING_NONE, $this->padding - $payloadLength);

        return pack('n*', $paddingLength).str_pad($payload, $this->padding, "\0", STR_PAD_LEFT);
    }

    protected function prepareHeaders(RequestInterface $request, ServerKey $serverKey, string $salt): RequestInterface
    {
        return $request
            ->withAddedHeader('Crypto-Key', sprintf('dh=%s', Base64Url::encode($serverKey->getPublicKey())))
            ->withAddedHeader('Encryption', 'salt='.Base64Url::encode($salt))
        ;
    }

    protected function prepareBody(string $encryptedText, ServerKey $serverKey, string $tag, string $salt): string
    {
        return $encryptedText.$tag;
    }
}
