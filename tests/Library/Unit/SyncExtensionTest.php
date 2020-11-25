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

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
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
        $logger = self::createMock(LoggerInterface::class);
        $logger
            ->expects(static::once())
            ->method('debug')
            ->with('Sending asynchronous notification')
    ;

        $request = self::createMock(RequestInterface::class);
        $request
            ->expects(static::once())
            ->method('withHeader')
            ->with('Prefer', 'respond-async')
            ->willReturnSelf()
        ;

        $notification = self::createMock(Notification::class);
        $notification
            ->expects(static::once())
            ->method('isAsync')
            ->willReturn(true)
        ;
        $subscription = self::createMock(Subscription::class);

        $extension = PreferAsyncExtension::create()
            ->setLogger($logger)
            ->process($request, $notification, $subscription)
        ;
    }

    /**
     * @test
     */
    public function asyncIsNotSetInHeader(): void
    {
        $logger = self::createMock(LoggerInterface::class);
        $logger
            ->expects(static::once())
            ->method('debug')
            ->with('Sending synchronous notification')
        ;

        $request = self::createMock(RequestInterface::class);
        $request
            ->expects(static::never())
            ->method('withHeader')
            ->willReturnSelf()
        ;

        $notification = self::createMock(Notification::class);
        $notification
            ->expects(static::once())
            ->method('isAsync')
            ->willReturn(false)
        ;
        $subscription = self::createMock(Subscription::class);

        PreferAsyncExtension::create()
            ->setLogger($logger)
            ->process($request, $notification, $subscription)
        ;
    }
}
