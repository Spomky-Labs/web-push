<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use WebPush\Notification;
use WebPush\PreferAsyncExtension;
use WebPush\RequestData;
use WebPush\Subscription;

/**
 * @internal
 */
final class SyncExtensionTest extends TestCase
{
    #[Test]
    public function asyncIsSetInHeader(): void
    {
        // Given
        $requestData = new RequestData();
        $notification = Notification::create()
            ->async()
        ;
        $subscription = Subscription::create('https://foo.bar');

        // When
        PreferAsyncExtension::create()
            ->process($requestData, $notification, $subscription)
        ;

        // Then
        static::assertSame('respond-async', $requestData->getHeaders()['Prefer']);
    }

    #[Test]
    public function asyncIsNotSetInHeader(): void
    {
        //Given
        $requestData = new RequestData();
        $notification = Notification::create()
            ->sync()
        ;
        $subscription = Subscription::create('https://foo.bar');

        // When
        PreferAsyncExtension::create()
            ->process($requestData, $notification, $subscription)
        ;

        // Then
        static::assertArrayNotHasKey('Prefer', $requestData->getHeaders());
    }
}
