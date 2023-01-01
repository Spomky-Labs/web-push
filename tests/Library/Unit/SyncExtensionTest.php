<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use WebPush\Notification;
use WebPush\PreferAsyncExtension;
use WebPush\Subscription;

/**
 * @internal
 */
final class SyncExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function asyncIsSetInHeader(): void
    {
        // Given
        $request = new Request('POST', 'https://foo.bar');
        $notification = Notification::create()
            ->async()
        ;
        $subscription = Subscription::create('https://foo.bar');

        // When
        $request = PreferAsyncExtension::create()
            ->process($request, $notification, $subscription)
        ;

        // Then
        static::assertSame('respond-async', $request->getHeaderLine('prefer'));
    }

    /**
     * @test
     */
    public function asyncIsNotSetInHeader(): void
    {
        //Given
        $request = new Request('POST', 'https://foo.bar');
        $notification = Notification::create()
            ->sync()
        ;
        $subscription = Subscription::create('https://foo.bar');

        // When
        $request = PreferAsyncExtension::create()
            ->process($request, $notification, $subscription)
        ;

        // Then
        static::assertFalse($request->hasHeader('prefer'));
    }
}
