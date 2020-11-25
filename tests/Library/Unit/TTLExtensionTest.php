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

namespace WebPush\Tests\Library\Unit;

use function array_key_exists;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use WebPush\Notification;
use WebPush\Subscription;
use WebPush\TTLExtension;

/**
 * @internal
 * @group Unit
 * @group Library
 */
final class TTLExtensionTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataTTLIsSetInHeader
     */
    public function ttlIsSetInHeader(int $ttl): void
    {
        $logger = self::createMock(LoggerInterface::class);
        $logger
            ->expects(static::once())
            ->method('debug')
            ->with('Processing with the TTL extension', static::callback(static function (array $data) use ($ttl): bool {
                if (!array_key_exists('TTL', $data)) {
                    return false;
                }

                return ((string) $ttl) === $data['TTL'];
            }))
        ;

        $request = self::createMock(RequestInterface::class);
        $request
            ->expects(static::once())
            ->method('withHeader')
            ->with('TTL', static::equalTo((string) $ttl))
            ->willReturnSelf()
        ;

        $notification = self::createMock(Notification::class);
        $notification
            ->expects(static::once())
            ->method('getTTL')
            ->willReturn($ttl)
        ;
        $subscription = self::createMock(Subscription::class);

        TTLExtension::create()
            ->setLogger($logger)
            ->process($request, $notification, $subscription)
        ;
    }

    /**
     * @return array<int, array<int, int>>
     */
    public function dataTTLIsSetInHeader(): array
    {
        return [
            [0],
            [10],
            [3600],
        ];
    }
}
