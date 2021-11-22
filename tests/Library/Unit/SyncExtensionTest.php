<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
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
        $logger = new TestLogger();
        $request = new Request('POST', 'https://foo.bar');
        $notification = Notification::create()
            ->async()
        ;
        $subscription = Subscription::create('https://foo.bar');

        $request = PreferAsyncExtension::create()
            ->setLogger($logger)
            ->process($request, $notification, $subscription)
        ;

        static::assertSame('respond-async', $request->getHeaderLine('prefer'));
        static::assertCount(1, $logger->records);
        static::assertSame('debug', $logger->records[0]['level']);
        static::assertSame('Sending asynchronous notification', $logger->records[0]['message']);
    }

    /**
     * @test
     */
    public function asyncIsNotSetInHeader(): void
    {
        $logger = new TestLogger();
        $request = new Request('POST', 'https://foo.bar');
        $notification = Notification::create()
            ->sync()
        ;
        $subscription = Subscription::create('https://foo.bar');

        $request = PreferAsyncExtension::create()
            ->setLogger($logger)
            ->process($request, $notification, $subscription)
        ;

        static::assertFalse($request->hasHeader('prefer'));
        static::assertCount(1, $logger->records);
        static::assertSame('debug', $logger->records[0]['level']);
        static::assertSame('Sending synchronous notification', $logger->records[0]['message']);
    }
}
