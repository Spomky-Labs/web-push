<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use WebPush\Notification;
use WebPush\RequestData;
use WebPush\Subscription;
use WebPush\UrgencyExtension;

/**
 * @internal
 */
final class UrgencyExtensionTest extends TestCase
{
    #[Test]
    #[DataProvider('dataUrgencyIsSetInHeader')]
    public function urgencyIsSetInHeader(string $urgency): void
    {
        // Given
        $requestData = new RequestData();

        $notification = Notification::create()
            ->withUrgency($urgency)
        ;
        $subscription = Subscription::create('https://foo.bar');

        // When
        UrgencyExtension::create()
            ->process($requestData, $notification, $subscription)
        ;

        // Then
        static::assertSame($urgency, $requestData->getHeaders()['Urgency']);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function dataUrgencyIsSetInHeader(): array
    {
        return [
            [Notification::URGENCY_VERY_LOW],
            [Notification::URGENCY_LOW],
            [Notification::URGENCY_NORMAL],
            [Notification::URGENCY_HIGH],
        ];
    }
}
