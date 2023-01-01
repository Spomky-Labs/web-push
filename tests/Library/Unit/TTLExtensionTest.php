<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
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
        // Given
        $request = new Request('POST', 'https://foo.bar');

        $notification = Notification::create()
            ->withTTL($ttl)
        ;
        $subscription = Subscription::create('https://foo.bar');

        // When
        $request = TTLExtension::create()
            ->process($request, $notification, $subscription)
        ;

        // Then
        static::assertSame((string) $ttl, $request->getHeaderLine('ttl'));
    }

    /**
     * @return array<int, array<int, int>>
     */
    public function dataTTLIsSetInHeader(): array
    {
        return [[0], [10], [3600]];
    }
}
