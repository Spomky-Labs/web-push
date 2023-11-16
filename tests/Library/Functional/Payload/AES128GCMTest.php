<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Functional\Payload;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Clock\NativeClock;
use WebPush\Base64Url;
use WebPush\Exception\OperationException;
use WebPush\Payload\AES128GCM;
use WebPush\Payload\ServerKey;
use WebPush\RequestData;
use WebPush\Subscription;
use WebPush\Utils;
use function chr;
use function count;
use function openssl_decrypt;
use function ord;
use function preg_match;
use function unpack;
use const OPENSSL_RAW_DATA;

/**
 * @internal
 */
final class AES128GCMTest extends TestCase
{
    #[Test]
    public function paddingLengthToHigh(): void
    {
        static::expectException(OperationException::class);
        static::expectExceptionMessage('Invalid padding size');

        AES128GCM::create(new NativeClock())->customPadding(3994);
    }

    #[Test]
    public function paddingLengthToLow(): void
    {
        static::expectException(OperationException::class);
        static::expectExceptionMessage('Invalid padding size');

        AES128GCM::create(new NativeClock())->customPadding(-1);
    }

    #[Test]
    public function missingUserAgentPublicKey(): void
    {
        static::expectException(OperationException::class);
        static::expectExceptionMessage('The user-agent public key is missing');

        $requestData = new RequestData();
        $subscription = Subscription::create('https://foo.bar')
            ->withContentEncodings(['aes128gcm'])
        ;

        AES128GCM::create(new NativeClock())->encode('', $requestData, $subscription);
    }

    #[Test]
    public function missingUserAgentAuthenticationToken(): void
    {
        static::expectException(OperationException::class);
        static::expectExceptionMessage('The user-agent authentication token is missing');

        $requestData = new RequestData();
        $subscription = Subscription::create('https://foo.bar')
            ->withContentEncodings(['aes128gcm'])
        ;
        $subscription->setKey(
            'p256dh',
            'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcx aOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw4'
        );

        AES128GCM::create(new NativeClock())->encode('', $requestData, $subscription);
    }

    /**
     * @see https://tests.peter.sh/push-encryption-verifier/
     */
    #[Test]
    public function decryptPayloadCorrectly(): void
    {
        $body = Base64Url::decode(
            'DGv6ra1nlYgDCS1FRnbzlwAAEABBBP4z9KsN6nGRTbVYI_c7VJSPQTBtkgcy27mlmlMoZIIgDll6e3vCYLocInmYWAmS6TlzAC8wEqKK6PBru3jl7A_yl95bQpu6cVPTpK4Mqgkf1CXztLVBSt2Ks3oZwbuwXPXLWyouBWLVWGNWQexSgSxsj_Qulcy4a-fN'
        );
        $userAgentPrivateKey = Base64Url::decode('q1dXpw3UpT5VOmu_cf_v6ih07Aems3njxI-JWgLcM94');
        $userAgentPublicKey = Base64Url::decode(
            'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcx aOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw4'
        );
        $userAgentAuthToken = Base64Url::decode('BTBZMqHH6r4Tts7J_aSIgg');
        $expectedPayload = 'When I grow up, I want to be a watermelon';

        $requestData = new RequestData();
        $requestData->setBody($body);

        $payload = $this->decryptRequest(
            $requestData,
            $userAgentAuthToken,
            $userAgentPublicKey,
            $userAgentPrivateKey,
            true
        );
        static::assertSame($expectedPayload, $payload);
    }

    /**
     * @see https://tests.peter.sh/push-encryption-verifier/
     */
    #[Test]
    #[DataProvider('dataEncryptPayload')]
    public function encryptPayload(
        string $userAgentPrivateKey,
        string $userAgentPublicKey,
        string $userAgentAuthToken,
        string $payload,
        string $padding,
        CacheItemPoolInterface $cache
    ): void {
        // Given
        $subscription = Subscription::create('https://foo.bar')
            ->withContentEncodings(['aes128gcm'])
        ;
        $subscription->setKey('p256dh', $userAgentPublicKey);
        $subscription->setKey('auth', $userAgentAuthToken);

        $encoder = AES128GCM::create(new NativeClock());

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
        static::assertSame('aes128gcm', $encoder->name());

        $requestData = new RequestData();

        // When
        $encoder
            ->encode($payload, $requestData, $subscription)
        ;

        // Then
        $decryptedPayload = $this->decryptRequest(
            $requestData,
            Base64Url::decode($userAgentAuthToken),
            Base64Url::decode($userAgentPublicKey),
            Base64Url::decode($userAgentPrivateKey),
            true
        );
        static::assertSame($payload, $decryptedPayload);
    }

    #[Test]
    public function largePayloadForbidden(): void
    {
        $request = null;
        static::expectException(OperationException::class);
        static::expectExceptionMessage('The size of payload must not be greater than 4096 bytes.');

        $userAgentPrivateKey = 'q1dXpw3UpT5VOmu_cf_v6ih07Aems3njxI-JWgLcM94';
        $userAgentPublicKey = 'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcx aOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw4';
        $userAgentAuthToken = 'BTBZMqHH6r4Tts7J_aSIgg';

        $subscription = Subscription::create('https://foo.bar')
            ->withContentEncodings(['aes128gcm'])
        ;
        $subscription->setKey('p256dh', $userAgentPublicKey);
        $subscription->setKey('auth', $userAgentAuthToken);

        $encoder = AES128GCM::create(new NativeClock());

        static::assertSame('aes128gcm', $encoder->name());
        $payload = str_pad('', 3994, '0');

        $requestData = new RequestData();
        $encoder->encode($payload, $requestData, $subscription);

        $decryptedPayload = $this->decryptRequest(
            $request,
            Base64Url::decode($userAgentAuthToken),
            Base64Url::decode($userAgentPublicKey),
            Base64Url::decode($userAgentPrivateKey),
            true
        );

        static::assertSame($payload, $decryptedPayload);
    }

    /**
     * @return array<int, array<int, CacheItemPoolInterface|LoggerInterface|string>>
     */
    public static function dataEncryptPayload(): iterable
    {
        $withoutCache = self::getMissingCache();
        $withCache = self::getExistingCache();
        $uaPrivateKey = 'q1dXpw3UpT5VOmu_cf_v6ih07Aems3njxI-JWgLcM94';
        $uaPublicKey = 'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcx aOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw4';
        $uaAuthSecret = 'BTBZMqHH6r4Tts7J_aSIgg';
        $payload = 'When I grow up, I want to be a watermelon';

        yield [$uaPrivateKey, $uaPublicKey, $uaAuthSecret, $payload, 'noPadding', $withoutCache];
        yield [$uaPrivateKey, $uaPublicKey, $uaAuthSecret, str_pad('', 3993, '1'), 'noPadding', $withoutCache];
        yield [$uaPrivateKey, $uaPublicKey, $uaAuthSecret, $payload, 'recommendedPadding', $withoutCache];
        yield [$uaPrivateKey, $uaPublicKey, $uaAuthSecret, $payload, 'maxPadding', $withoutCache];
        yield [$uaPrivateKey, $uaPublicKey, $uaAuthSecret, $payload, 'customPadding', $withoutCache];
        yield [$uaPrivateKey, $uaPublicKey, $uaAuthSecret, $payload, 'noPadding', $withCache];
        yield [$uaPrivateKey, $uaPublicKey, $uaAuthSecret, $payload, 'recommendedPadding', $withCache];
        yield [$uaPrivateKey, $uaPublicKey, $uaAuthSecret, $payload, 'maxPadding', $withCache];
    }

    private function decryptRequest(
        RequestData $requestData,
        string $authSecret,
        string $receiverPublicKey,
        string $receiverPrivateKey,
        bool $inverted = false
    ): string {
        $ciphertext = $requestData->getBody();

        // Salt
        $salt = mb_substr($ciphertext, 0, 16, '8bit');
        static::assertSame(mb_strlen($salt, '8bit'), 16);

        // Record size
        $rs = mb_substr($ciphertext, 16, 4, '8bit');
        $rs = unpack('N', $rs)[1];
        static::assertSame(4096, $rs);

        // idlen
        $idlen = ord(mb_substr($ciphertext, 20, 1, '8bit'));

        //keyid
        $keyid = mb_substr($ciphertext, 21, $idlen, '8bit');

        // IKM
        $keyInfo = 'WebPush: info' . chr(0) . ($inverted ? $receiverPublicKey . $keyid : $keyid . $receiverPublicKey);
        $ikm = Utils::computeIKM($keyInfo, $authSecret, $keyid, $receiverPrivateKey, $receiverPublicKey);

        // We remove the header
        $ciphertext = mb_substr($ciphertext, 16 + 4 + 1 + $idlen, null, '8bit');

        // We compute the PRK
        $prk = hash_hmac('sha256', $ikm, $salt, true);

        $cekInfo = 'Content-Encoding: aes128gcm' . chr(0);
        $cek = mb_substr(hash_hmac('sha256', $cekInfo . chr(1), $prk, true), 0, 16, '8bit');

        $nonceInfo = 'Content-Encoding: nonce' . chr(0);
        $nonce = mb_substr(hash_hmac('sha256', $nonceInfo . chr(1), $prk, true), 0, 12, '8bit');

        $C = mb_substr($ciphertext, 0, -16, '8bit');
        $T = mb_substr($ciphertext, -16, null, '8bit');

        $rawData = openssl_decrypt($C, 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $T);

        $matches = [];
        $r = preg_match('/^(.*)(\x02\x00*)$/', $rawData, $matches);
        if ($r !== 1 || count($matches) !== 3) {
            throw new InvalidArgumentException('Invalid data');
        }

        return $matches[1];
    }

    private static function getMissingCache(): CacheItemPoolInterface
    {
        return new NullAdapter();
    }

    private static function getExistingCache(): CacheItemPoolInterface
    {
        $cache = new ArrayAdapter();
        $item = $cache->getItem('WEB_PUSH_PAYLOAD_ENCRYPTION');
        $item->set(
            ServerKey::create(
                Base64Url::decode(
                    'BNuH4FkvKM50iG9sNLmJxSJL-H5B7KzxdpVOMp8OCmJZIaiZhXWFEolBD3xAXpJbjqMuny5jznfDnjYKueWngnM'
                ),
                Base64Url::decode('Bw10H72jYRnlGZQytw8ruC9uJzqkWJqlOyFEEqQqYZ0')
            )
        );
        $cache->save($item);

        return $cache;
    }
}
