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

namespace WebPush\Tests\Library\Functional\VAPID;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use Safe\DateTimeImmutable;
use WebPush\Base64Url;
use WebPush\VAPID\WebTokenProvider;

/**
 * @internal
 * @group Functional
 * @group Library
 */
final class WebTokenProviderTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataInvalidKey
     */
    public function invalidKey(string $publicKey, string $privateKey, string $expectedMessage): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage($expectedMessage);

        WebTokenProvider::create(
            Base64Url::encode($publicKey),
            Base64Url::encode($privateKey)
        );
    }

    /**
     * @test
     * @dataProvider dataComputeHeader
     */
    public function computeHeader(string $publicKey, string $privateKey): void
    {
        $expiresAt = new DateTimeImmutable('@1580253757');

        $logger = new TestLogger();

        $header = WebTokenProvider::create($publicKey, $privateKey)
            ->setLogger($logger)
            ->computeHeader([
                'aud' => 'audience',
                'sub' => 'subject',
                'exp' => $expiresAt->getTimestamp(),
            ])
        ;

        static::assertStringStartsWith('eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NiJ9.eyJhdWQiOiJhdWRpZW5jZSIsInN1YiI6InN1YmplY3QiLCJleHAiOjE1ODAyNTM3NTd9.', $header->getToken());
        static::assertEquals($publicKey, $header->getKey());
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function dataComputeHeader(): array
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
    public function dataInvalidKey(): array
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
