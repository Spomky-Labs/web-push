<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use Http\Mock\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use WebPush\ExtensionManager;
use WebPush\Notification;
use WebPush\Subscription;
use WebPush\Tests\TestLogger;
use WebPush\WebPush;

/**
 * @internal
 */
final class WebPushTest extends TestCase
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
        static::assertSame('debug', $logger->records[0]['level']);
        static::assertSame('Sending notification', $logger->records[0]['message']);
        static::assertInstanceOf(Notification::class, $logger->records[0]['context']['notification']);
        static::assertInstanceOf(Subscription::class, $logger->records[0]['context']['subscription']);
        static::assertSame('debug', $logger->records[1]['level']);
        static::assertSame('Request ready', $logger->records[1]['message']);
        static::assertInstanceOf(RequestInterface::class, $logger->records[1]['context']['request']);
        static::assertSame('debug', $logger->records[2]['level']);
        static::assertSame('Response received', $logger->records[2]['message']);
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
        static::assertSame('debug', $logger->records[0]['level']);
        static::assertSame('Sending notification', $logger->records[0]['message']);
        static::assertInstanceOf(Notification::class, $logger->records[0]['context']['notification']);
        static::assertInstanceOf(Subscription::class, $logger->records[0]['context']['subscription']);
        static::assertSame('debug', $logger->records[1]['level']);
        static::assertSame('Request ready', $logger->records[1]['message']);
        static::assertInstanceOf(RequestInterface::class, $logger->records[1]['context']['request']);
        static::assertSame('debug', $logger->records[2]['level']);
        static::assertSame('Response received', $logger->records[2]['message']);
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
        static::assertSame('debug', $logger->records[0]['level']);
        static::assertSame('Sending notification', $logger->records[0]['message']);
        static::assertInstanceOf(Notification::class, $logger->records[0]['context']['notification']);
        static::assertInstanceOf(Subscription::class, $logger->records[0]['context']['subscription']);
        static::assertSame('debug', $logger->records[1]['level']);
        static::assertSame('Request ready', $logger->records[1]['message']);
        static::assertInstanceOf(RequestInterface::class, $logger->records[1]['context']['request']);
        static::assertSame('debug', $logger->records[2]['level']);
        static::assertSame('Response received', $logger->records[2]['message']);
        static::assertInstanceOf(ResponseInterface::class, $logger->records[2]['context']['response']);
        static::assertFalse($report->isSuccess());
        static::assertSame($notification, $report->getNotification());
        static::assertSame($subscription, $report->getSubscription());
    }
}
