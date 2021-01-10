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

namespace WebPush\Tests\Library\Functional;

use Http\Mock\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use WebPush\ExtensionManager;
use WebPush\Notification;
use WebPush\Payload\AES128GCM;
use WebPush\Payload\AESGCM;
use WebPush\Payload\PayloadExtension;
use WebPush\PreferAsyncExtension;
use WebPush\Subscription;
use WebPush\TopicExtension;
use WebPush\TTLExtension;
use WebPush\UrgencyExtension;
use WebPush\VAPID\VAPIDExtension;
use WebPush\VAPID\WebTokenProvider;
use WebPush\WebPush;

/**
 * @internal
 * @group Functional
 * @group Library
 * @covers \WebPush\WebPush
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
        $subscription->setKey('auth', 'wSfP1pfACMwFesCEfJx4-w');
        $subscription->setKey('p256dh', 'BIlDpD05YLrVPXfANOKOCNSlTvjpb5vdFo-1e0jNcbGlFrP49LyOjYyIIAZIVCDAHEcX-135b859bdsse-PgosU');

        $notification = Notification::create()
            ->sync()
            ->highUrgency()
            ->withTopic('topic')
            ->withPayload('Hello World')
            ->withTTL(3600)
        ;

        $client = new Client();
        $client->addResponse(new Response());
        $service = $this->getService($client);

        $report = $service->send($notification, $subscription);

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
        static::assertEquals(['4096'], $request->getHeader('content-length'));
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
        $subscription->setKey('auth', 'wSfP1pfACMwFesCEfJx4-w');
        $subscription->setKey('p256dh', 'BIlDpD05YLrVPXfANOKOCNSlTvjpb5vdFo-1e0jNcbGlFrP49LyOjYyIIAZIVCDAHEcX-135b859bdsse-PgosU');

        $notification = Notification::create()
            ->sync()
            ->highUrgency()
            ->withTopic('topic')
            ->withPayload('Hello World')
            ->withTTL(3600)
        ;

        $client = new Client();
        $client->addResponse(new Response());
        $service = $this->getService($client);

        $report = $service->send($notification, $subscription);

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
        static::assertEquals(['4095'], $request->getHeader('content-length'));
        static::assertStringStartsWith('vapid t=', $request->getHeaderLine('authorization'));
    }

    private function getService(ClientInterface $client): WebPush
    {
        $extensionManager = ExtensionManager::create()
            ->add(TTLExtension::create())
            ->add(UrgencyExtension::create())
            ->add(TopicExtension::create())
            ->add(PreferAsyncExtension::create())
            ->add(
                PayloadExtension::create()
                    ->addContentEncoding(AESGCM::create()->maxPadding())
                    ->addContentEncoding(AES128GCM::create()->maxPadding())
            )
            ->add(VAPIDExtension::create(
                'http://localhost:8000',
                WebTokenProvider::create(
                    'BB4W1qfBi7MF_Lnrc6i2oL-glAuKF4kevy9T0k2vyKV4qvuBrN3T6o9-7-NR3mKHwzDXzD3fe7XvIqIU1iADpGQ',
                    'C40jLFSa5UWxstkFvdwzT3eHONE2FIJSEsVIncSCAqU'
                )
            ))
        ;

        $requestFactory = new Psr17Factory();

        return new WebPush($client, $requestFactory, $extensionManager);
    }
}
