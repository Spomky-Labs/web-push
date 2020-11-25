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

namespace WebPush\Tests\Library\Functional;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use WebPush\Base64Url;
use WebPush\Keys;
use WebPush\Notification;
use WebPush\SimpleWebPush;
use WebPush\Subscription;

/**
 * @internal
 * @group Functional
 * @group Library
 */
class WebPushTest extends TestCase
{
    /**
     * @test
     */
    public function aNotificationCanBeSent(): void
    {
        $response = self::createMock(ResponseInterface::class);
        $response
            ->expects(static::never())
            ->method(static::anything())
        ;

        $body = self::createMock(StreamInterface::class);
        $body
            ->expects(static::once())
            ->method('write')
            ->willReturnSelf()
        ;

        $request = self::createMock(RequestInterface::class);
        $request
            ->expects(static::once())
            ->method('getBody')
            ->willReturn($body)
        ;
        $request
            ->expects(static::exactly(2))
            ->method('withAddedHeader')
            ->withConsecutive(
                ['Crypto-Key'],
                ['Authorization'],
            )
            ->willReturnSelf()
        ;
        $request
            ->expects(static::exactly(7))
            ->method('withHeader')
            ->withConsecutive(
                ['TTL', '3600'],
                ['Topic', 'topic'],
                ['Urgency', 'high'],
                ['Content-Type', 'application/octet-stream'],
                ['Content-Encoding', 'aesgcm'],
                ['Encryption', static::callback(static function (string $data): bool {
                    $pos = mb_strpos($data, 'salt=', 0, '8bit');
                    if (0 !== $pos) {
                        return false;
                    }
                    $salt = Base64Url::decode(mb_substr($data, 5, null));

                    return 16 === mb_strlen($salt, '8bit');
                })],
                ['Content-Length', '3070'],
            )
            ->willReturnSelf()
        ;

        $subscription = self::createMock(Subscription::class);
        $subscription
            ->expects(static::exactly(2))
            ->method('getEndpoint')
            ->willReturn('https://foo.bar')
        ;
        $subscription
            ->expects(static::once())
            ->method('getContentEncoding')
            ->willReturn('aesgcm')
        ;

        $keys = new Keys();
        $keys
            ->set('auth', 'wSfP1pfACMwFesCEfJx4-w')
            ->set('p256dh', 'BIlDpD05YLrVPXfANOKOCNSlTvjpb5vdFo-1e0jNcbGlFrP49LyOjYyIIAZIVCDAHEcX-135b859bdsse-PgosU')
        ;
        $subscription
            ->expects(static::once())
            ->method('getKeys')
            ->willReturn($keys)
        ;

        $notification = Notification::create()
            ->sync()
            ->highUrgency()
            ->withTopic('topic')
            ->withPayload('Hello World')
            ->withTTL(3600)
        ;

        $client = self::createMock(ClientInterface::class);
        $client
            ->expects(static::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response)
        ;

        $requestFactory = self::createMock(RequestFactoryInterface::class);
        $requestFactory
            ->expects(static::once())
            ->method('createRequest')
            ->with('POST', 'https://foo.bar')
            ->willReturn($request)
        ;

        $report = SimpleWebPush::create($client, $requestFactory)
            ->enableVapid(
                'http://localhost:8000',
                'BB4W1qfBi7MF_Lnrc6i2oL-glAuKF4kevy9T0k2vyKV4qvuBrN3T6o9-7-NR3mKHwzDXzD3fe7XvIqIU1iADpGQ',
                'C40jLFSa5UWxstkFvdwzT3eHONE2FIJSEsVIncSCAqU'
            )
            ->send($notification, $subscription)
        ;

        static::assertSame($notification, $report->getNotification());
        static::assertSame($subscription, $report->getSubscription());
    }

    /**
     * @test
     */
    public function aNotificationCannotBeSent(): void
    {
        $response = self::createMock(ResponseInterface::class);

        $body = self::createMock(StreamInterface::class);
        $body
            ->expects(static::once())
            ->method('write')
            ->willReturnSelf()
        ;

        $request = self::createMock(RequestInterface::class);
        $request
            ->expects(static::once())
            ->method('getBody')
            ->willReturn($body)
        ;
        $request
            ->expects(static::exactly(2))
            ->method('withAddedHeader')
            ->withConsecutive(
                ['Crypto-Key'],
                ['Authorization'],
            )
            ->willReturnSelf()
        ;
        $request
            ->expects(static::exactly(6))
            ->method('withHeader')
            ->withConsecutive(
                ['TTL', '3600'],
                ['Topic', 'topic'],
                ['Urgency', 'high'],
                ['Content-Type', 'application/octet-stream'],
                ['Content-Encoding', 'aes128gcm'],
                ['Content-Length', '3154'],
            )
            ->willReturnSelf()
        ;

        $subscription = self::createMock(Subscription::class);
        $subscription
            ->expects(static::exactly(2))
            ->method('getEndpoint')
            ->willReturn('https://foo.bar')
        ;
        $subscription
            ->expects(static::once())
            ->method('getContentEncoding')
            ->willReturn('aes128gcm')
        ;

        $keys = new Keys();
        $keys
            ->set('auth', 'wSfP1pfACMwFesCEfJx4-w')
            ->set('p256dh', 'BIlDpD05YLrVPXfANOKOCNSlTvjpb5vdFo-1e0jNcbGlFrP49LyOjYyIIAZIVCDAHEcX-135b859bdsse-PgosU')
        ;
        $subscription
            ->expects(static::once())
            ->method('getKeys')
            ->willReturn($keys)
        ;

        $notification = Notification::create()
            ->sync()
            ->highUrgency()
            ->withTopic('topic')
            ->withPayload('Hello World')
            ->withTTL(3600)
        ;

        $client = self::createMock(ClientInterface::class);
        $client
            ->expects(static::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response)
        ;

        $requestFactory = self::createMock(RequestFactoryInterface::class);
        $requestFactory
            ->expects(static::once())
            ->method('createRequest')
            ->with('POST', 'https://foo.bar')
            ->willReturn($request)
        ;

        $report = SimpleWebPush::create($client, $requestFactory)
            ->enableVapid(
                'http://localhost:8000',
                'BB4W1qfBi7MF_Lnrc6i2oL-glAuKF4kevy9T0k2vyKV4qvuBrN3T6o9-7-NR3mKHwzDXzD3fe7XvIqIU1iADpGQ',
                'C40jLFSa5UWxstkFvdwzT3eHONE2FIJSEsVIncSCAqU'
            )
            ->send($notification, $subscription)
        ;

        static::assertSame($notification, $report->getNotification());
        static::assertSame($subscription, $report->getSubscription());
    }

    /**
     * @test
     */
    public function vapidCannotBeEnabledMoreThanOnce(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('VAPID has already been enabled');

        $client = self::createMock(ClientInterface::class);
        $requestFactory = self::createMock(RequestFactoryInterface::class);

        SimpleWebPush::create($client, $requestFactory)
            ->enableVapid(
                'http://localhost:8000',
                'BB4W1qfBi7MF_Lnrc6i2oL-glAuKF4kevy9T0k2vyKV4qvuBrN3T6o9-7-NR3mKHwzDXzD3fe7XvIqIU1iADpGQ',
                'C40jLFSa5UWxstkFvdwzT3eHONE2FIJSEsVIncSCAqU'
            )
            ->enableVapid(
                'http://localhost:8000',
                'BB4W1qfBi7MF_Lnrc6i2oL-glAuKF4kevy9T0k2vyKV4qvuBrN3T6o9-7-NR3mKHwzDXzD3fe7XvIqIU1iADpGQ',
                'C40jLFSa5UWxstkFvdwzT3eHONE2FIJSEsVIncSCAqU'
            )
        ;
    }
}
