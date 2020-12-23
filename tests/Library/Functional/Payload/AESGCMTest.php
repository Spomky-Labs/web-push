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

namespace WebPush\Tests\Library\Functional\Payload;

use function chr;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use function Safe\openssl_decrypt;
use function Safe\sprintf;
use function Safe\unpack;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use WebPush\Base64Url;
use WebPush\Payload\AESGCM;
use WebPush\Payload\ServerKey;
use WebPush\Subscription;
use WebPush\Utils;

/**
 * @internal
 * @group Functional
 * @group Library
 */
final class AESGCMTest extends TestCase
{
    /**
     * @test
     */
    public function paddingLengthToHigh(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Invalid padding size');

        AESGCM::create()->customPadding(4079);
    }

    /**
     * @test
     */
    public function paddingLengthToLow(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Invalid padding size');

        AESGCM::create()->customPadding(-1);
    }

    /**
     * @test
     */
    public function missingUserAgentPublicKey(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('The user-agent public key is missing');

        $request = new Request('POST', 'https://foo.bar');
        $subscription = Subscription::create('https://foo.bar')
            ->withContentEncodings(['aesgcm'])
        ;

        AESGCM::create()->encode('', $request, $subscription);
    }

    /**
     * @test
     */
    public function missingUserAgentAuthenticationToken(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('The user-agent authentication token is missing');

        $request = new Request('POST', 'https://foo.bar');
        $subscription = Subscription::create('https://foo.bar')
            ->withContentEncodings(['aesgcm'])
        ;
        $subscription->getKeys()->set('p256dh', 'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcx aOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw4');

        AESGCM::create()->encode('', $request, $subscription);
    }

    /**
     * @test
     * @dataProvider dataEncryptPayload
     *
     * @see https://tests.peter.sh/push-encryption-verifier/
     */
    public function encryptPayload(string $userAgentPrivateKey, string $userAgentPublicKey, string $userAgentAuthToken, string $payload, string $padding, CacheItemPoolInterface $cache): void
    {
        $subscription = Subscription::create('https://foo.bar')
            ->withContentEncodings(['aesgcm'])
        ;
        $subscription->getKeys()->set('p256dh', $userAgentPublicKey);
        $subscription->getKeys()->set('auth', $userAgentAuthToken);

        $encoder = AESGCM::create();

        switch ($padding) {
            case 'noPadding':
                $encoder->noPadding();
                break;
            case 'recommendedPadding':
                $encoder->recommendedPadding();
                break;
            case 'maxPadding':
                $encoder->maxPadding();
                break;
            case 'customPadding':
                $encoder->customPadding(1024);
                break;
            default:
                break;
        }

        $encoder->setCache($cache);

        static::assertEquals('aesgcm', $encoder->name());

        $request = new Request('POST', 'https://foo.bar');
        $request = $encoder->encode($payload, $request, $subscription);

        $decryptedPayload = $this->decryptRequest(
            $request,
            Base64Url::decode($userAgentAuthToken),
            Base64Url::decode($userAgentPublicKey),
            Base64Url::decode($userAgentPrivateKey),
            true
        );

        static::assertEquals($payload, $decryptedPayload);
    }

    /**
     * @test
     */
    public function largePayloadForbidden(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('The size of payload must not be greater than 4096 bytes.');

        $request = new Request('POST', 'https://foo.bar');

        $subscription = Subscription::create('https://foo.bar');
        $subscription->getKeys()->set('p256dh', 'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcx aOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw4');
        $subscription->getKeys()->set('auth', 'BTBZMqHH6r4Tts7J_aSIgg');

        $payload = str_pad('', 4079, '0');

        AESGCM::create()
            ->encode($payload, $request, $subscription)
        ;
    }

    /**
     * @return array<int, array<int, CacheItemPoolInterface|LoggerInterface|string>>
     */
    public function dataEncryptPayload(): array
    {
        $withoutCache = $this->getMissingCache();
        $withCache = $this->getExistingCache();
        $uaPrivateKey = 'q1dXpw3UpT5VOmu_cf_v6ih07Aems3njxI-JWgLcM94';
        $uaPublicKey = 'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcx aOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw4';
        $uaAuthSecret = 'BTBZMqHH6r4Tts7J_aSIgg';
        $payload = 'When I grow up, I want to be a watermelon';

        return [
            [
                $uaPrivateKey,
                $uaPublicKey,
                $uaAuthSecret,
                $payload,
                'noPadding',
                $withoutCache,
            ],
            [
                $uaPrivateKey,
                $uaPublicKey,
                $uaAuthSecret,
                str_pad('', 4078, '1'),
                'noPadding',
                $withoutCache,
            ],
            [
                $uaPrivateKey,
                $uaPublicKey,
                $uaAuthSecret,
                $payload,
                'recommendedPadding',
                $withoutCache,
            ],
            [
                $uaPrivateKey,
                $uaPublicKey,
                $uaAuthSecret,
                $payload,
                'maxPadding',
                $withoutCache,
            ],
            [
                $uaPrivateKey,
                $uaPublicKey,
                $uaAuthSecret,
                $payload,
                'customPadding',
                $withoutCache,
            ],
            [
                $uaPrivateKey,
                $uaPublicKey,
                $uaAuthSecret,
                $payload,
                'noPadding',
                $withCache,
            ],
            [
                $uaPrivateKey,
                $uaPublicKey,
                $uaAuthSecret,
                $payload,
                'recommendedPadding',
                $withCache,
            ],
            [
                $uaPrivateKey,
                $uaPublicKey,
                $uaAuthSecret,
                $payload,
                'maxPadding',
                $withCache,
            ],
        ];
    }

    private function decryptRequest(RequestInterface $request, string $authSecret, string $receiverPublicKey, string $receiverPrivateKey, bool $inverted = false): string
    {
        $requestBody = $request->getBody();
        $requestBody->rewind();

        $ciphertext = $requestBody->getContents();
        $salt = Base64Url::decode(mb_substr($request->getHeaderLine('encryption'), 5));
        $keyid = Base64Url::decode(mb_substr($request->getHeaderLine('crypto-key'), 3));

        $context = sprintf('%s%s%s%s',
            "P-256\0\0A",
            $inverted ? $receiverPublicKey : $keyid,
            "\0A",
            $inverted ? $keyid : $receiverPublicKey
        );

        // IKM
        $keyInfo = 'Content-Encoding: auth'.chr(0);
        $ikm = Utils::computeIKM($keyInfo, $authSecret, $keyid, $receiverPrivateKey, $receiverPublicKey);

        // We compute the PRK
        $prk = hash_hmac('sha256', $ikm, $salt, true);

        $cekInfo = 'Content-Encoding: aesgcm'.chr(0).$context;
        $cek = mb_substr(hash_hmac('sha256', $cekInfo.chr(1), $prk, true), 0, 16, '8bit');

        $nonceInfo = 'Content-Encoding: nonce'.chr(0).$context;
        $nonce = mb_substr(hash_hmac('sha256', $nonceInfo.chr(1), $prk, true), 0, 12, '8bit');

        $C = mb_substr($ciphertext, 0, -16, '8bit');
        $T = mb_substr($ciphertext, -16, null, '8bit');

        $rawData = openssl_decrypt($C, 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $T);
        $padding = mb_substr($rawData, 0, 2, '8bit');
        $paddingLength = unpack('n', $padding)[1];

        return mb_substr($rawData, 2 + $paddingLength, null, '8bit');
    }

    private function getMissingCache(): CacheItemPoolInterface
    {
        return new NullAdapter();
    }

    private function getExistingCache(): CacheItemPoolInterface
    {
        $cache = new ArrayAdapter();
        $item = $cache->getItem('WEB_PUSH_PAYLOAD_ENCRYPTION');
        $item->set(
            new ServerKey(
                Base64Url::decode('BNuH4FkvKM50iG9sNLmJxSJL-H5B7KzxdpVOMp8OCmJZIaiZhXWFEolBD3xAXpJbjqMuny5jznfDnjYKueWngnM'),
                Base64Url::decode('Bw10H72jYRnlGZQytw8ruC9uJzqkWJqlOyFEEqQqYZ0')
            )
        );
        $cache->save($item);

        return $cache;
    }
}
