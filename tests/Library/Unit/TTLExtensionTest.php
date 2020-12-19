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

use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
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
        $logger = new TestLogger();
        $request = new Request('POST', 'https://foo.bar');

        $notification = Notification::create()
            ->withTTL($ttl)
        ;
        $subscription = Subscription::create('https://foo.bar');

        $request = TTLExtension::create()
            ->setLogger($logger)
            ->process($request, $notification, $subscription)
        ;

        static::assertEquals($ttl, $request->getHeaderLine('ttl'));
        static::assertCount(1, $logger->records);
        static::assertEquals('debug', $logger->records[0]['level']);
        static::assertEquals('Processing with the TTL extension', $logger->records[0]['message']);
        static::assertEquals($ttl, $logger->records[0]['context']['TTL']);
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
