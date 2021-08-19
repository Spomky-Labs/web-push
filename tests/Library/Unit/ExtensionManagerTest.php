<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use WebPush\ExtensionManager;
use WebPush\Notification;
use WebPush\PreferAsyncExtension;
use WebPush\Subscription;
use WebPush\TTLExtension;

/**
 * @internal
 * @group Unit
 * @group Library
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
            ->add(new TTLExtension())
            ->add(new PreferAsyncExtension())
            ->process($request, $notification, $subscription)
        ;

        static::assertCount(4, $logger->records);
        static::assertEquals('debug', $logger->records[0]['level']);
        static::assertEquals('Extension added', $logger->records[0]['message']);
        static::assertEquals('debug', $logger->records[1]['level']);
        static::assertEquals('Extension added', $logger->records[1]['message']);
        static::assertEquals('debug', $logger->records[2]['level']);
        static::assertEquals('Processing the request', $logger->records[2]['message']);
        static::assertEquals('debug', $logger->records[3]['level']);
        static::assertEquals('Processing done', $logger->records[3]['message']);
    }
}
