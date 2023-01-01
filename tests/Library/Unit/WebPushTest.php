<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use Http\Mock\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use WebPush\ExtensionManager;
use WebPush\Notification;
use WebPush\Subscription;
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
        //Given
        $subscription = Subscription::create('https://foo.bar');
        $notification = Notification::create();

        $client = new Client();
        $client->addResponse(new Response(201));
        $requestFactory = new Psr17Factory();

        $extensionManager = ExtensionManager::create();
        $webPush = WebPush::create($client, $requestFactory, $extensionManager);

        // When
        $report = $webPush
            ->send($notification, $subscription)
        ;

        // Then
        static::assertTrue($report->isSuccess());
        static::assertSame($notification, $report->getNotification());
        static::assertSame($subscription, $report->getSubscription());
    }

    /**
     * @test
     */
    public function aNotificationCanBeSentAsync(): void
    {
        // Given
        $subscription = Subscription::create('https://foo.bar');
        $notification = Notification::create();

        $client = new Client();
        $client->addResponse(new Response(202));
        $requestFactory = new Psr17Factory();

        $extensionManager = ExtensionManager::create();
        $webPush = WebPush::create($client, $requestFactory, $extensionManager);

        // When
        $report = $webPush
            ->send($notification, $subscription)
        ;

        // Then
        static::assertTrue($report->isSuccess());
        static::assertSame($notification, $report->getNotification());
        static::assertSame($subscription, $report->getSubscription());
    }

    /**
     * @test
     */
    public function aNotificationCannotBeSent(): void
    {
        // Given
        $subscription = Subscription::create('https://foo.bar');
        $notification = Notification::create();

        $client = new Client();
        $client->addResponse(new Response(409));
        $requestFactory = new Psr17Factory();

        $extensionManager = ExtensionManager::create();
        $webPush = WebPush::create($client, $requestFactory, $extensionManager);

        // When
        $report = $webPush
            ->send($notification, $subscription)
        ;

        // Then
        static::assertFalse($report->isSuccess());
        static::assertSame($notification, $report->getNotification());
        static::assertSame($subscription, $report->getSubscription());
    }
}
