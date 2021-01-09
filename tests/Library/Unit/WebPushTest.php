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

namespace WebPush\Tests\Library\Unit;

use Http\Mock\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\Test\TestLogger;
use WebPush\ExtensionManager;
use WebPush\Notification;
use WebPush\Subscription;
use WebPush\WebPush;

/**
 * @internal
 * @group Unit
 * @group Library
 */
class WebPushTest extends TestCase
{
    /**
     * @test
     */
    public function aNotificationCanBeSent(): void
    {
        $subscription = Subscription::create('https://foo.bar');
        $notification = Notification::create();

        $client = new Client();
        $client->addResponse(new Response(201));
        $requestFactory = new Psr17Factory();

        $extensionManager = ExtensionManager::create();
        $logger = new TestLogger();

        $webPush = WebPush::create($client, $requestFactory, $extensionManager);
        $report = $webPush
            ->setLogger($logger)
            ->send($notification, $subscription)
        ;

        static::assertCount(3, $logger->records);
        static::assertEquals('debug', $logger->records[0]['level']);
        static::assertEquals('Sending notification', $logger->records[0]['message']);
        static::assertInstanceOf(Notification::class, $logger->records[0]['context']['notification']);
        static::assertInstanceOf(Subscription::class, $logger->records[0]['context']['subscription']);
        static::assertEquals('debug', $logger->records[1]['level']);
        static::assertEquals('Request ready', $logger->records[1]['message']);
        static::assertInstanceOf(RequestInterface::class, $logger->records[1]['context']['request']);
        static::assertEquals('debug', $logger->records[2]['level']);
        static::assertEquals('Response received', $logger->records[2]['message']);
        static::assertInstanceOf(ResponseInterface::class, $logger->records[2]['context']['response']);
        static::assertTrue($report->isSuccess());
        static::assertSame($notification, $report->getNotification());
        static::assertSame($subscription, $report->getSubscription());
    }

    /**
     * @test
     */
    public function aNotificationCanBeSentAsync(): void
    {
        $subscription = Subscription::create('https://foo.bar');
        $notification = Notification::create();

        $client = new Client();
        $client->addResponse(new Response(202));
        $requestFactory = new Psr17Factory();

        $extensionManager = ExtensionManager::create();
        $logger = new TestLogger();

        $webPush = WebPush::create($client, $requestFactory, $extensionManager);
        $report = $webPush
            ->setLogger($logger)
            ->send($notification, $subscription)
        ;

        static::assertCount(3, $logger->records);
        static::assertEquals('debug', $logger->records[0]['level']);
        static::assertEquals('Sending notification', $logger->records[0]['message']);
        static::assertInstanceOf(Notification::class, $logger->records[0]['context']['notification']);
        static::assertInstanceOf(Subscription::class, $logger->records[0]['context']['subscription']);
        static::assertEquals('debug', $logger->records[1]['level']);
        static::assertEquals('Request ready', $logger->records[1]['message']);
        static::assertInstanceOf(RequestInterface::class, $logger->records[1]['context']['request']);
        static::assertEquals('debug', $logger->records[2]['level']);
        static::assertEquals('Response received', $logger->records[2]['message']);
        static::assertInstanceOf(ResponseInterface::class, $logger->records[2]['context']['response']);
        static::assertTrue($report->isSuccess());
        static::assertSame($notification, $report->getNotification());
        static::assertSame($subscription, $report->getSubscription());
    }

    /**
     * @test
     */
    public function aNotificationCannotBeSent(): void
    {
        $subscription = Subscription::create('https://foo.bar');
        $notification = Notification::create();

        $client = new Client();
        $client->addResponse(new Response(409));
        $requestFactory = new Psr17Factory();

        $extensionManager = ExtensionManager::create();
        $logger = new TestLogger();

        $webPush = WebPush::create($client, $requestFactory, $extensionManager);
        $report = $webPush
            ->setLogger($logger)
            ->send($notification, $subscription)
        ;

        static::assertCount(3, $logger->records);
        static::assertEquals('debug', $logger->records[0]['level']);
        static::assertEquals('Sending notification', $logger->records[0]['message']);
        static::assertInstanceOf(Notification::class, $logger->records[0]['context']['notification']);
        static::assertInstanceOf(Subscription::class, $logger->records[0]['context']['subscription']);
        static::assertEquals('debug', $logger->records[1]['level']);
        static::assertEquals('Request ready', $logger->records[1]['message']);
        static::assertInstanceOf(RequestInterface::class, $logger->records[1]['context']['request']);
        static::assertEquals('debug', $logger->records[2]['level']);
        static::assertEquals('Response received', $logger->records[2]['message']);
        static::assertInstanceOf(ResponseInterface::class, $logger->records[2]['context']['response']);
        static::assertFalse($report->isSuccess());
        static::assertSame($notification, $report->getNotification());
        static::assertSame($subscription, $report->getSubscription());
    }
}
