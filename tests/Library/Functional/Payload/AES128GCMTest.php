<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2020-2021 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace WebPush\Tests\Library\Functional\Payload;

use function chr;
use function count;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use function ord;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;
use function Safe\openssl_decrypt;
use function Safe\preg_match;
use function Safe\unpack;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use WebPush\Base64Url;
use WebPush\Payload\AES128GCM;
use WebPush\Payload\ServerKey;
use WebPush\Subscription;
use WebPush\Utils;

/**
 * @internal
 * @group Functional
 * @group Library
 */
final class AES128GCMTest extends TestCase
{
    /**
     * @test
     */
    public function paddingLengthToHigh(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Invalid padding size');

        AES128GCM::create()->customPadding(3994);
    }

    /**
     * @test
     */
    public function paddingLengthToLow(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Invalid padding size');

        AES128GCM::create()->customPadding(-1);
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
            ->withContentEncodings(['aes128gcm'])
        ;

        AES128GCM::create()->encode('', $request, $subscription);
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
            ->withContentEncodings(['aes128gcm'])
        ;
        $subscription->getKeys()->set('p256dh', 'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcx aOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw4');

        AES128GCM::create()->encode('', $request, $subscription);
    }

    /**
     * @test
     *
     * @see https://tests.peter.sh/push-encryption-verifier/
     */
    public function decryptPayloadCorrectly(): void
    {
        $body = Base64Url::decode('DGv6ra1nlYgDCS1FRnbzlwAAEABBBP4z9KsN6nGRTbVYI_c7VJSPQTBtkgcy27mlmlMoZIIgDll6e3vCYLocInmYWAmS6TlzAC8wEqKK6PBru3jl7A_yl95bQpu6cVPTpK4Mqgkf1CXztLVBSt2Ks3oZwbuwXPXLWyouBWLVWGNWQexSgSxsj_Qulcy4a-fN');
        $userAgentPrivateKey = Base64Url::decode('q1dXpw3UpT5VOmu_cf_v6ih07Aems3njxI-JWgLcM94');
        $userAgentPublicKey = Base64Url::decode('BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcx aOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw4');
        $userAgentAuthToken = Base64Url::decode('BTBZMqHH6r4Tts7J_aSIgg');
        $expectedPayload = 'When I grow up, I want to be a watermelon';

        $request = new Request('POST', 'https://foo.bar', [], $body);

        $payload = $this->decryptRequest($request, $userAgentAuthToken, $userAgentPublicKey, $userAgentPrivateKey, true);
        static::assertEquals($expectedPayload, $payload);
    }

    /**
     * @test
     * @dataProvider dataEncryptPayload
     *
     * @see https://tests.peter.sh/push-encryption-verifier/
     */
    public function encryptPayload(string $userAgentPrivateKey, string $userAgentPublicKey, string $userAgentAuthToken, string $payload, string $padding, CacheItemPoolInterface $cache): void
    {
        $logger = new TestLogger();
        $subscription = Subscription::create('https://foo.bar')
            ->withContentEncodings(['aes128gcm'])
        ;
        $subscription->getKeys()->set('p256dh', $userAgentPublicKey);
        $subscription->getKeys()->set('auth', $userAgentAuthToken);

        $encoder = AES128GCM::create();

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
        static::assertEquals('aes128gcm', $encoder->name());

        $request = new Request('POST', 'https://foo.bar');
        $encoder
            ->setLogger($logger)
            ->encode($payload, $request, $subscription)
        ;

        $decryptedPayload = $this->decryptRequest(
            $request,
            Base64Url::decode($userAgentAuthToken),
            Base64Url::decode($userAgentPublicKey),
            Base64Url::decode($userAgentPrivateKey),
            true
        );

        static::assertEquals($payload, $decryptedPayload);

        static::assertGreaterThanOrEqual(13, count($logger->records));
        foreach ($logger->records as $record) {
            static::assertEquals('debug', $record['level']);
        }
    }

    /**
     * @test
     */
    public function largePayloadForbidden(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('The size of payload must not be greater than 4096 bytes.');

        $userAgentPrivateKey = 'q1dXpw3UpT5VOmu_cf_v6ih07Aems3njxI-JWgLcM94';
        $userAgentPublicKey = 'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcx aOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw4';
        $userAgentAuthToken = 'BTBZMqHH6r4Tts7J_aSIgg';

        $subscription = Subscription::create('https://foo.bar')
            ->withContentEncodings(['aes128gcm'])
        ;
        $subscription->getKeys()->set('p256dh', $userAgentPublicKey);
        $subscription->getKeys()->set('auth', $userAgentAuthToken);

        $encoder = AES128GCM::create();

        static::assertEquals('aes128gcm', $encoder->name());
        $payload = str_pad('', 3994, '0');

        $request = new Request('POST', 'https://foo.bar');
        $encoder->encode($payload, $request, $subscription);

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
                str_pad('', 3993, '1'),
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

        // Salt
        $salt = mb_substr($ciphertext, 0, 16, '8bit');
        static::assertEquals(mb_strlen($salt, '8bit'), 16);

        // Record size
        $rs = mb_substr($ciphertext, 16, 4, '8bit');
        $rs = unpack('N', $rs)[1];
        static::assertEquals(4096, $rs);

        // idlen
        $idlen = ord(mb_substr($ciphertext, 20, 1, '8bit'));

        //keyid
        $keyid = mb_substr($ciphertext, 21, $idlen, '8bit');

        // IKM
        $keyInfo = 'WebPush: info'.chr(0).($inverted ? $receiverPublicKey.$keyid : $keyid.$receiverPublicKey);
        $ikm = Utils::computeIKM($keyInfo, $authSecret, $keyid, $receiverPrivateKey, $receiverPublicKey);

        // We remove the header
        $ciphertext = mb_substr($ciphertext, 16 + 4 + 1 + $idlen, null, '8bit');

        // We compute the PRK
        $prk = hash_hmac('sha256', $ikm, $salt, true);

        $cekInfo = 'Content-Encoding: aes128gcm'.chr(0);
        $cek = mb_substr(hash_hmac('sha256', $cekInfo.chr(1), $prk, true), 0, 16, '8bit');

        $nonceInfo = 'Content-Encoding: nonce'.chr(0);
        $nonce = mb_substr(hash_hmac('sha256', $nonceInfo.chr(1), $prk, true), 0, 12, '8bit');

        $C = mb_substr($ciphertext, 0, -16, '8bit');
        $T = mb_substr($ciphertext, -16, null, '8bit');

        $rawData = openssl_decrypt($C, 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $T);

        $matches = [];
        $r = preg_match('/^(.*)(\x02\x00*)$/', $rawData, $matches);
        if (1 !== $r || 3 !== count($matches)) {
            throw new InvalidArgumentException('Invalid data');
        }

        return $matches[1];
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
