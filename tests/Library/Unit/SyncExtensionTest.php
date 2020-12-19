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
use WebPush\PreferAsyncExtension;
use WebPush\Subscription;

/**
 * @internal
 * @group Unit
 * @group Library
 */
final class SyncExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function asyncIsSetInHeader(): void
    {
        $logger = new TestLogger();
        $request = new Request('POST', 'https://foo.bar');
        $notification = Notification::create()
            ->async()
        ;
        $subscription = Subscription::create('https://foo.bar');

        $request = PreferAsyncExtension::create()
            ->setLogger($logger)
            ->process($request, $notification, $subscription)
        ;

        static::assertEquals('respond-async', $request->getHeaderLine('prefer'));
        static::assertCount(1, $logger->records);
        static::assertEquals('debug', $logger->records[0]['level']);
        static::assertEquals('Sending asynchronous notification', $logger->records[0]['message']);
    }

    /**
     * @test
     */
    public function asyncIsNotSetInHeader(): void
    {
        $logger = new TestLogger();
        $request = new Request('POST', 'https://foo.bar');
        $notification = Notification::create()
            ->sync()
        ;
        $subscription = Subscription::create('https://foo.bar');

        $request = PreferAsyncExtension::create()
            ->setLogger($logger)
            ->process($request, $notification, $subscription)
        ;

        static::assertFalse($request->hasHeader('prefer'));
        static::assertCount(1, $logger->records);
        static::assertEquals('debug', $logger->records[0]['level']);
        static::assertEquals('Sending synchronous notification', $logger->records[0]['message']);
    }
}
