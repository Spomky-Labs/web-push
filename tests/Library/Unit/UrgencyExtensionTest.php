<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use WebPush\Notification;
use WebPush\Subscription;
use WebPush\UrgencyExtension;

/**
 * @internal
 */
final class UrgencyExtensionTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataUrgencyIsSetInHeader
     */
    public function urgencyIsSetInHeader(string $urgency): void
    {
        // Given
        $request = new Request('POST', 'https://foo.bar');

        $notification = Notification::create()
            ->withUrgency($urgency)
        ;
        $subscription = Subscription::create('https://foo.bar');

        // When
        $request = UrgencyExtension::create()
            ->process($request, $notification, $subscription)
        ;

        // Then
        static::assertSame($urgency, $request->getHeaderLine('urgency'));
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function dataUrgencyIsSetInHeader(): array
    {
        return [
            [Notification::URGENCY_VERY_LOW],
            [Notification::URGENCY_LOW],
            [Notification::URGENCY_NORMAL],
            [Notification::URGENCY_HIGH],
        ];
    }
}
