<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use WebPush\Notification;
use WebPush\Subscription;
use WebPush\TTLExtension;

/**
 * @internal
 */
final class TTLExtensionTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataTTLIsSetInHeader
     */
    public function ttlIsSetInHeader(int $ttl): void
    {
        $logger = new TestLogger();
        $request = new Request('POST', 'https://foo.bar');

        $notification = Notification::create()
            ->withTTL($ttl)
        ;
        $subscription = Subscription::create('https://foo.bar');

        $request = TTLExtension::create()
            ->setLogger($logger)
            ->process($request, $notification, $subscription)
        ;

        static::assertSame((string) $ttl, $request->getHeaderLine('ttl'));
        static::assertCount(1, $logger->records);
        static::assertSame('debug', $logger->records[0]['level']);
        static::assertSame('Processing with the TTL extension', $logger->records[0]['message']);
        static::assertSame((string) $ttl, $logger->records[0]['context']['TTL']);
    }

    /**
     * @return array<int, array<int, int>>
     */
    public function dataTTLIsSetInHeader(): array
    {
        return [[0], [10], [3600]];
    }
}
