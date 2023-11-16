<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use WebPush\Base64Url;
use WebPush\Exception\OperationException;
use WebPush\Utils;
use function chr;

/**
 * @internal
 */
final class UtilsTest extends TestCase
{
    #[Test]
    public function publicKeyToPEM(): void
    {
        $publicKey = Base64Url::decode(
            'BB4W1qfBi7MF_Lnrc6i2oL-glAuKF4kevy9T0k2vyKV4qvuBrN3T6o9-7-NR3mKHwzDXzD3fe7XvIqIU1iADpGQ'
        );
        $pem = Utils::publicKeyToPEM($publicKey);

        static::assertSame(<<<'CODE_SAMPLE'
-----BEGIN PUBLIC KEY-----
MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEHhbWp8GLswX8uetzqLagv6CUC4oX
iR6/L1PSTa/IpXiq+4Gs3dPqj37v41HeYofDMNfMPd97te8iohTWIAOkZA==
-----END PUBLIC KEY-----

CODE_SAMPLE
            , $pem);
    }

    #[Test]
    public function privateKeyToPEM(): void
    {
        $privateKey = Base64Url::decode('C40jLFSa5UWxstkFvdwzT3eHONE2FIJSEsVIncSCAqU');
        $publicKey = Base64Url::decode(
            'BB4W1qfBi7MF_Lnrc6i2oL-glAuKF4kevy9T0k2vyKV4qvuBrN3T6o9-7-NR3mKHwzDXzD3fe7XvIqIU1iADpGQ'
        );
        $pem = Utils::privateKeyToPEM($privateKey, $publicKey);

        static::assertSame(<<<'CODE_SAMPLE'
-----BEGIN EC PRIVATE KEY-----
MHcCAQEEIAuNIyxUmuVFsbLZBb3cM093hzjRNhSCUhLFSJ3EggKloAoGCCqGSM49
AwEHoUQDQgAEHhbWp8GLswX8uetzqLagv6CUC4oXiR6/L1PSTa/IpXiq+4Gs3dPq
j37v41HeYofDMNfMPd97te8iohTWIAOkZA==
-----END EC PRIVATE KEY-----

CODE_SAMPLE
            , $pem);
    }

    #[Test]
    public function privateKeyToPEMAdjusted(): void
    {
        $privateKey = '';
        $publicKey = str_pad('', 65, chr(0));
        $pem = Utils::privateKeyToPEM($privateKey, $publicKey);

        static::assertSame(<<<'CODE_SAMPLE'
-----BEGIN EC PRIVATE KEY-----
MHcCAQEEIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAoAoGCCqGSM49
AwEHoUQDQgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA==
-----END EC PRIVATE KEY-----

CODE_SAMPLE
            , $pem);
    }

    /**
     * @see https://tools.ietf.org/html/rfc8291#section-5
     */
    #[Test]
    public function computeIKM(): void
    {
        $senderPublicKey = Base64Url::decode(
            'BP4z9KsN6nGRTbVYI_c7VJSPQTBtkgcy27mlmlMoZIIgDll6e3vCYLocInmYWAmS6TlzAC8wEqKK6PBru3jl7A8'
        );
        $senderPrivateKey = Base64Url::decode('yfWPiYE-n46HLnH0KqZOF1fJJU3MYrct3AELtAQ-oRw');
        $receiverPublicKey = Base64Url::decode(
            'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcx aOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw4'
        );
        $authSecret = Base64Url::decode('BTBZMqHH6r4Tts7J_aSIgg');

        $expectedIKM = Base64Url::decode('S4lYMb_L0FxCeq0WhDx813KgSYqU26kOyzWUdsXYyrg');

        $keyInfo = 'WebPush: info' . chr(0) . $receiverPublicKey . $senderPublicKey;
        $ikm = Utils::computeIKM($keyInfo, $authSecret, $receiverPublicKey, $senderPrivateKey, $senderPublicKey);
        static::assertSame($expectedIKM, $ikm);
    }

    #[Test]
    public function unableToComputeIKM(): void
    {
        static::expectException(OperationException::class);
        static::expectExceptionMessage('Unable to compute the agreement key');

        $senderPublicKey = '';
        $senderPrivateKey = str_pad('', 65, chr(0));
        $receiverPublicKey = Base64Url::decode(
            'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcx aOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw4'
        );
        $authSecret = Base64Url::decode('BTBZMqHH6r4Tts7J_aSIgg');

        $keyInfo = 'WebPush: info' . chr(0) . $receiverPublicKey . $senderPublicKey;
        Utils::computeIKM($keyInfo, $authSecret, $receiverPublicKey, $senderPrivateKey, $senderPublicKey);
    }
}
