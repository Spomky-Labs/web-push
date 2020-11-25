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

namespace WebPush\VAPID;

use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Key\InMemory;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function Safe\json_encode;
use function Safe\sprintf;
use WebPush\Base64Url;
use WebPush\Loggable;
use WebPush\Utils;

final class LcobucciProvider implements JWSProvider, Loggable
{
    private string $publicKey;
    private LoggerInterface $logger;
    private Key $key;

    public function __construct(string $publicKey, string $privateKey)
    {
        $this->publicKey = $publicKey;
        $pem = Utils::privateKeyToPEM(
            Base64Url::decode($privateKey),
            Base64Url::decode($publicKey)
        );
        $this->key = InMemory::plainText($pem);
        $this->logger = new NullLogger();
    }

    public static function create(string $publicKey, string $privateKey): self
    {
        return new self($publicKey, $privateKey);
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function computeHeader(array $claims): Header
    {
        $this->logger->debug('Computing the JWS');
        $signer = Sha256::create();
        $header = json_encode(['typ' => 'JWT', 'alg' => 'ES256'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $payload = json_encode($claims, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $dataToSign = sprintf(
            '%s.%s',
            Base64Url::encode($header),
            Base64Url::encode($payload)
        );
        $signature = $signer->sign($dataToSign, $this->key);
        $token = sprintf(
            '%s.%s',
            $dataToSign,
            Base64Url::encode($signature)
        );

        $this->logger->debug('JWS computed', ['token' => $token, 'key' => $this->publicKey]);

        return new Header(
            $token,
            $this->publicKey
        );
    }
}
