<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use Nyholm\Psr7\Request;
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
    /**
     * @test
     */
    public function topicIsSetInHeader(): void
    {
        // Given
        $request = new Request('POST', 'https://foo.bar');
        $notification = Notification::create();
        $subscription = Subscription::create('https://foo.bar');

        // When
        $request = ExtensionManager::create()
            ->add(TTLExtension::create())
            ->process($request, $notification, $subscription)
        ;

        // Then
        static::assertTrue($request->hasHeader('TTL'));
        static::assertSame('0', $request->getHeaderLine('TTL'));
    }
}
