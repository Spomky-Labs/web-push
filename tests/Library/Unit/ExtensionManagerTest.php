<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use WebPush\ExtensionManager;
use WebPush\Notification;
use WebPush\PreferAsyncExtension;
use WebPush\Subscription;
use WebPush\Tests\TestLogger;
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
        $logger = new TestLogger();
        $request = new Request('POST', 'https://foo.bar');
        $notification = Notification::create();
        $subscription = Subscription::create('https://foo.bar');

        ExtensionManager::create()
            ->setLogger($logger)
            ->add(TTLExtension::create())
            ->add(PreferAsyncExtension::create())
            ->process($request, $notification, $subscription)
        ;

        static::assertCount(4, $logger->records);
        static::assertSame('debug', $logger->records[0]['level']);
        static::assertSame('Extension added', $logger->records[0]['message']);
        static::assertSame('debug', $logger->records[1]['level']);
        static::assertSame('Extension added', $logger->records[1]['message']);
        static::assertSame('debug', $logger->records[2]['level']);
        static::assertSame('Processing the request', $logger->records[2]['message']);
        static::assertSame('debug', $logger->records[3]['level']);
        static::assertSame('Processing done', $logger->records[3]['message']);
    }
}
