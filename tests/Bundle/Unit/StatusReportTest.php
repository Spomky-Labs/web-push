<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Response\MockResponse;
use WebPush\Bundle\Service\StatusReport;
use WebPush\Notification;
use WebPush\Subscription;

/**
 * @internal
 */
final class StatusReportTest extends TestCase
{
    #[Test]
    #[DataProvider('dataReport')]
    public function report(int $statusCode, bool $isSuccess, bool $hasExpired): void
    {
        $subscription = Subscription::create('https://foo.bar');
        $notification = Notification::create();
        $response = new MockResponse(
            '',
            [
                'http_code' => $statusCode,
                'response_headers' => [
                    'location' => ['https://foo.bar'],
                    'link' => ['https://link.1'],
                ],
            ]
        );
        $report = new StatusReport($subscription, $notification, $response);

        static::assertSame($subscription, $report->getSubscription());
        static::assertSame($notification, $report->getNotification());
        static::assertSame('https://foo.bar', $report->getLocation());
        static::assertSame(['https://link.1'], $report->getLinks());
        static::assertSame($isSuccess, $report->isSuccess());
        static::assertSame($hasExpired, $report->isSubscriptionExpired());
    }

    /**
     * @return array[]
     */
    public static function dataReport(): array
    {
        return [
            [199, false, false],
            [200, true, false],
            [201, true, false],
            [202, true, false],
            [299, true, false],
            [300, false, false],
            [301, false, false],
            [400, false, false],
            [403, false, false],
            [404, false, true],
            [405, false, false],
            [409, false, false],
            [410, false, true],
            [411, false, false],
        ];
    }
}