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

namespace WebPush\Tests\Library\Functional\VAPID;

use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use Safe\DateTimeImmutable;
use WebPush\VAPID\LcobucciProvider;

/**
 * @internal
 * @group Functional
 * @group Library
 */
final class LcobucciProviderTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataComputeHeader
     */
    public function computeHeader(string $publicKey, string $privateKey): void
    {
        $expiresAt = DateTimeImmutable::createFromFormat('Y-m-d\TH:i:sP', '2020-01-28T16:22:37-07:00');

        $logger = new TestLogger();

        $header = LcobucciProvider::create($publicKey, $privateKey)
            ->setLogger($logger)
            ->computeHeader([
                'aud' => 'audience',
                'sub' => 'subject',
                'exp' => $expiresAt->getTimestamp(),
            ])
        ;

        static::assertCount(2, $logger->records);
        static::assertEquals('debug', $logger->records[0]['level']);
        static::assertEquals('Computing the JWS', $logger->records[0]['message']);
        static::assertEquals('debug', $logger->records[1]['level']);
        static::assertEquals('JWS computed', $logger->records[1]['message']);
        static::assertEquals(['token', 'key'], array_keys($logger->records[1]['context']));

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
}
