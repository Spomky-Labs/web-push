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

use Http\Mock\Client;
use InvalidArgumentException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use WebPush\Notification;
use WebPush\SimpleWebPush;
use WebPush\Subscription;

/**
 * @internal
 * @group Functional
 * @group Library
 * @covers \WebPush\SimpleWebPush
 */
class WebPushTest extends TestCase
{
    /**
     * @test
     */
    public function aNotificationCanBeSent(): void
    {
        $subscription = Subscription::create('https://foo.bar')
            ->withContentEncodings(['aesgcm'])
        ;
        $subscription->getKeys()->set('auth', 'wSfP1pfACMwFesCEfJx4-w');
        $subscription->getKeys()->set('p256dh', 'BIlDpD05YLrVPXfANOKOCNSlTvjpb5vdFo-1e0jNcbGlFrP49LyOjYyIIAZIVCDAHEcX-135b859bdsse-PgosU');

        $notification = Notification::create()
            ->sync()
            ->highUrgency()
            ->withTopic('topic')
            ->withPayload('Hello World')
            ->withTTL(3600)
        ;

        $client = new Client();
        $client->addResponse(new Response());
        $requestFactory = new Psr17Factory();

        $report = SimpleWebPush::create($client, $requestFactory)
            ->enableVapid(
                'http://localhost:8000',
                'BB4W1qfBi7MF_Lnrc6i2oL-glAuKF4kevy9T0k2vyKV4qvuBrN3T6o9-7-NR3mKHwzDXzD3fe7XvIqIU1iADpGQ',
                'C40jLFSa5UWxstkFvdwzT3eHONE2FIJSEsVIncSCAqU'
            )
            ->send($notification, $subscription)
        ;

        $request = $report->getRequest();
        static::assertTrue($request->hasHeader('ttl'));
        static::assertTrue($request->hasHeader('topic'));
        static::assertTrue($request->hasHeader('urgency'));
        static::assertTrue($request->hasHeader('content-type'));
        static::assertTrue($request->hasHeader('content-encoding'));
        static::assertTrue($request->hasHeader('crypto-key'));
        static::assertTrue($request->hasHeader('encryption'));
        static::assertTrue($request->hasHeader('content-length'));
        static::assertTrue($request->hasHeader('authorization'));
        static::assertEquals(['3600'], $request->getHeader('ttl'));
        static::assertEquals(['topic'], $request->getHeader('topic'));
        static::assertEquals(['high'], $request->getHeader('urgency'));
        static::assertEquals(['application/octet-stream'], $request->getHeader('content-type'));
        static::assertEquals(['aesgcm'], $request->getHeader('content-encoding'));
        static::assertEquals(['3070'], $request->getHeader('content-length'));
        static::assertStringStartsWith('dh=', $request->getHeaderLine('crypto-key'));
        static::assertStringStartsWith('salt=', $request->getHeaderLine('encryption'));
        static::assertStringStartsWith('vapid t=', $request->getHeaderLine('authorization'));
    }

    /**
     * @test
     */
    public function aNotificationCannotBeSent(): void
    {
        $subscription = Subscription::create('https://foo.bar')
            ->withContentEncodings(['aes128gcm'])
        ;
        $subscription->getKeys()->set('auth', 'wSfP1pfACMwFesCEfJx4-w');
        $subscription->getKeys()->set('p256dh', 'BIlDpD05YLrVPXfANOKOCNSlTvjpb5vdFo-1e0jNcbGlFrP49LyOjYyIIAZIVCDAHEcX-135b859bdsse-PgosU');

        $notification = Notification::create()
            ->sync()
            ->highUrgency()
            ->withTopic('topic')
            ->withPayload('Hello World')
            ->withTTL(3600)
        ;

        $client = new Client();
        $client->addResponse(new Response());
        $requestFactory = new Psr17Factory();

        $report = SimpleWebPush::create($client, $requestFactory)
            ->enableVapid(
                'http://localhost:8000',
                'BB4W1qfBi7MF_Lnrc6i2oL-glAuKF4kevy9T0k2vyKV4qvuBrN3T6o9-7-NR3mKHwzDXzD3fe7XvIqIU1iADpGQ',
                'C40jLFSa5UWxstkFvdwzT3eHONE2FIJSEsVIncSCAqU'
            )
            ->send($notification, $subscription)
        ;

        $request = $report->getRequest();
        static::assertTrue($request->hasHeader('ttl'));
        static::assertTrue($request->hasHeader('topic'));
        static::assertTrue($request->hasHeader('urgency'));
        static::assertTrue($request->hasHeader('content-type'));
        static::assertTrue($request->hasHeader('content-encoding'));
        static::assertFalse($request->hasHeader('crypto-key'));
        static::assertFalse($request->hasHeader('encryption'));
        static::assertTrue($request->hasHeader('content-length'));
        static::assertTrue($request->hasHeader('authorization'));
        static::assertEquals(['3600'], $request->getHeader('ttl'));
        static::assertEquals(['topic'], $request->getHeader('topic'));
        static::assertEquals(['high'], $request->getHeader('urgency'));
        static::assertEquals(['application/octet-stream'], $request->getHeader('content-type'));
        static::assertEquals(['aes128gcm'], $request->getHeader('content-encoding'));
        static::assertEquals(['3154'], $request->getHeader('content-length'));
        static::assertStringStartsWith('vapid t=', $request->getHeaderLine('authorization'));
    }

    /**
     * @test
     */
    public function vapidCannotBeEnabledMoreThanOnce(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('VAPID has already been enabled');

        $client = new Client();
        $requestFactory = new Psr17Factory();

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
