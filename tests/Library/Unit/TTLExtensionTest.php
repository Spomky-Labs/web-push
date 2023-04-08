<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use WebPush\Notification;
use WebPush\RequestData;
use WebPush\Subscription;
use WebPush\TTLExtension;

/**
 * @internal
 */
final class TTLExtensionTest extends TestCase
{
    #[Test]
    #[DataProvider('dataTTLIsSetInHeader')]
    public function ttlIsSetInHeader(int $ttl): void
    {
        // Given
        $requestData = new RequestData();

        $notification = Notification::create()
            ->withTTL($ttl)
        ;
        $subscription = Subscription::create('https://foo.bar');

        // When
        TTLExtension::create()
            ->process($requestData, $notification, $subscription)
        ;

        // Then
        static::assertSame((string) $ttl, $requestData->getHeaders()['TTL']);
    }

    /**
     * @return array<int, array<int, int>>
     */
    public static function dataTTLIsSetInHeader(): array
    {
        return [[0], [10], [3600]];
    }
}
