<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use WebPush\ExtensionManager;
use WebPush\Notification;
use WebPush\Subscription;
use WebPush\WebPush;

/**
 * @internal
 */
final class WebPushTest extends TestCase
{
    #[Test]
    public function aNotificationCanBeSent(): void
    {
        //Given
        $subscription = Subscription::create('https://foo.bar');
        $notification = Notification::create();

        $client = new MockHttpClient();
        $client->setResponseFactory(fn () => new MockResponse('', [
            'http_code' => 201,
        ]));

        $extensionManager = ExtensionManager::create();
        $webPush = WebPush::create($client, $extensionManager);

        // When
        $report = $webPush
            ->send($notification, $subscription)
        ;

        // Then
        static::assertTrue($report->isSuccess());
        static::assertSame($notification, $report->getNotification());
        static::assertSame($subscription, $report->getSubscription());
    }

    #[Test]
    public function aNotificationCanBeSentAsync(): void
    {
        // Given
        $subscription = Subscription::create('https://foo.bar');
        $notification = Notification::create();

        $client = new MockHttpClient();
        $client->setResponseFactory(fn () => new MockResponse('', [
            'http_code' => 201,
        ]));

        $extensionManager = ExtensionManager::create();
        $webPush = WebPush::create($client, $extensionManager);

        // When
        $report = $webPush
            ->send($notification, $subscription)
        ;

        // Then
        static::assertTrue($report->isSuccess());
        static::assertSame($notification, $report->getNotification());
        static::assertSame($subscription, $report->getSubscription());
    }

    #[Test]
    public function aNotificationCannotBeSent(): void
    {
        // Given
        $subscription = Subscription::create('https://foo.bar');
        $notification = Notification::create();

        $client = new MockHttpClient();
        $client->setResponseFactory(fn () => new MockResponse('', [
            'http_code' => 409,
        ]));

        $extensionManager = ExtensionManager::create();
        $webPush = WebPush::create($client, $extensionManager);

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
