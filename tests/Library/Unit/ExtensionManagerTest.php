<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use WebPush\ExtensionManager;
use WebPush\Notification;
use WebPush\Subscription;
use WebPush\TTLExtension;

/**
 * @internal
 */
final class ExtensionManagerTest extends TestCase
{
    #[Test]
    public function topicIsSetInHeader(): void
    {
        // Given
        $notification = Notification::create();
        $subscription = Subscription::create('https://foo.bar');

        // When
        $requestData = ExtensionManager::create()
            ->add(TTLExtension::create())
            ->process($notification, $subscription)
        ;

        // Then
        static::assertArrayHasKey('TTL', $requestData->getHeaders());
        static::assertSame('0', $requestData->getHeaders()['TTL']);
    }
}
