<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Functional\VAPID;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use const STR_PAD_RIGHT;
use WebPush\Base64Url;
use WebPush\Exception\OperationException;
use WebPush\VAPID\LcobucciProvider;

/**
 * @internal
 */
final class LcobucciProviderTest extends TestCase
{
    #[Test]
    #[DataProvider('dataInvalidKey')]
    public function invalidKey(string $publicKey, string $privateKey, string $expectedMessage): void
    {
        static::expectException(OperationException::class);
        static::expectExceptionMessage($expectedMessage);

        LcobucciProvider::create(Base64Url::encode($publicKey), Base64Url::encode($privateKey));
    }

    #[Test]
    #[DataProvider('dataComputeHeader')]
    public function computeHeader(string $publicKey, string $privateKey): void
    {
        // Given
        $expiresAt = new DateTimeImmutable('@1580253757');

        // When
        $header = LcobucciProvider::create($publicKey, $privateKey)
            ->computeHeader([
                'aud' => 'audience',
                'sub' => 'subject',
                'exp' => $expiresAt->getTimestamp(),
            ])
        ;

        // Then
        static::assertStringStartsWith(
            'eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NiJ9.eyJhdWQiOiJhdWRpZW5jZSIsInN1YiI6InN1YmplY3QiLCJleHAiOjE1ODAyNTM3NTd9.',
            $header->getToken()
        );
        static::assertSame($publicKey, $header->getKey());
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function dataComputeHeader(): array
    {
        return [
            [
                'publicKey' => 'BDCgQkzSHClEg4otdckrN-duog2fAIk6O07uijwKr-w-4Etl6SRW2YiLUrN5vfvVHuhp7x8PxltmWWlbbM4IFyM',
                'privateKey' => '870MB6gfuTJ4HtUnUvYMyJpr5eUZNP4Bk43bVdj3eAE',
            ],
            [
                'publicKey' => 'BNFEvAnv7SfVGz42xFvdcu-z-W_3FVm_yRSGbEVtxVRRXqCBYJtvngQ8ZN-9bzzamxLjpbw7vuHcHTT2H98LwLM',
                'privateKey' => 'TcP5-SlbNbThgntDB7TQHXLslhaxav8Qqdd_Ar7VuNo',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function dataInvalidKey(): array
    {
        return [
            [
                'publicKey' => '',
                'privateKey' => str_pad('', 33, "\1"),
                'expectedMessage' => 'Invalid private key size',
            ],
            [
                'publicKey' => '',
                'privateKey' => str_pad('', 31, "\1"),
                'expectedMessage' => 'Invalid private key size',
            ],
            [
                'publicKey' => str_pad('', 66, "\1"),
                'privateKey' => str_pad('', 32, "\1"),
                'expectedMessage' => 'Invalid public key size',
            ],
            [
                'publicKey' => str_pad('', 64, "\1"),
                'privateKey' => str_pad('', 32, "\1"),
                'expectedMessage' => 'Invalid public key size',
            ],
            [
                'publicKey' => str_pad('', 65, "\1"),
                'privateKey' => str_pad('', 32, "\1"),
                'expectedMessage' => 'Invalid public key',
            ],
            [
                'publicKey' => str_pad("\3", 65, "\1", STR_PAD_RIGHT),
                'privateKey' => str_pad('', 32, "\1"),
                'expectedMessage' => 'Invalid public key',
            ],
            [
                'publicKey' => str_pad("\5", 65, "\1", STR_PAD_RIGHT),
                'privateKey' => str_pad('', 32, "\1"),
                'expectedMessage' => 'Invalid public key',
            ],
        ];
    }
}
