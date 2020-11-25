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

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use WebPush\Notification;
use WebPush\Payload\ContentEncoding;
use WebPush\Payload\PayloadExtension;
use WebPush\Subscription;

/**
 * @internal
 * @group Unit
 * @group Library
 */
final class PayloadExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function canProcessWithoutPayload(): void
    {
        $logger = self::createMock(LoggerInterface::class);
        $logger
            ->expects(static::exactly(2))
            ->method('debug')
            ->withConsecutive(
                ['Processing with payload'],
                ['No payload'],
            )
        ;

        $request = self::createMock(RequestInterface::class);
        $request
            ->expects(static::once())
            ->method('withHeader')
            ->with('Content-Length', '0')
            ->willReturnSelf()
        ;

        $notification = self::createMock(Notification::class);
        $notification
            ->expects(static::once())
            ->method('getPayload')
            ->willReturn(null)
        ;
        $subscription = self::createMock(Subscription::class);

        PayloadExtension::create()
            ->setLogger($logger)
            ->process($request, $notification, $subscription)
        ;
    }

    /**
     * @test
     */
    public function canProcessWithPayload(): void
    {
        $logger = self::createMock(LoggerInterface::class);
        $logger
            ->expects(static::exactly(2))
            ->method('debug')
            ->withConsecutive(
                ['Processing with payload'],
                ['Encoder found: aesgcm. Processing with the encoder.'],
            )
        ;

        $request = self::createMock(RequestInterface::class);
        $request
            ->expects(static::exactly(2))
            ->method('withHeader')
            ->withConsecutive(
                ['Content-Type', 'application/octet-stream'],
                ['Content-Encoding', 'aesgcm'],
            )
            ->willReturnSelf()
        ;

        $notification = self::createMock(Notification::class);
        $notification
            ->expects(static::once())
            ->method('getPayload')
            ->willReturn('Payload')
        ;
        $subscription = self::createMock(Subscription::class);
        $subscription
            ->expects(static::once())
            ->method('getContentEncoding')
            ->willReturn('aesgcm')
        ;

        $contentEncoding = self::createMock(ContentEncoding::class);
        $contentEncoding
            ->expects(static::once())
            ->method('name')
            ->willReturn('aesgcm')
        ;
        $contentEncoding
            ->expects(static::once())
            ->method('encode')
            ->with(
                'Payload',
                static::isInstanceOf(RequestInterface::class),
                static::isInstanceOf(Subscription::class)
            )
            ->willReturnArgument(1)
        ;

        PayloadExtension::create()
            ->setLogger($logger)
            ->addContentEncoding($contentEncoding)
            ->process($request, $notification, $subscription)
        ;
    }

    /**
     * @test
     */
    public function unsupportedContentEncoding(): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('The content encoding "aesgcm" is not supported');

        $logger = self::createMock(LoggerInterface::class);
        $logger
            ->expects(static::once())
            ->method('debug')
            ->withConsecutive(
                ['Processing with payload'],
            )
        ;

        $request = self::createMock(RequestInterface::class);
        $request
            ->expects(static::never())
            ->method(static::anything())
        ;

        $notification = self::createMock(Notification::class);
        $notification
            ->expects(static::once())
            ->method('getPayload')
            ->willReturn('Payload')
        ;
        $subscription = self::createMock(Subscription::class);
        $subscription
            ->expects(static::once())
            ->method('getContentEncoding')
            ->willReturn('aesgcm')
        ;

        $contentEncoding = self::createMock(ContentEncoding::class);
        $contentEncoding
            ->expects(static::never())
            ->method(static::anything())
        ;

        PayloadExtension::create()
            ->setLogger($logger)
            ->process($request, $notification, $subscription)
        ;
    }
}
