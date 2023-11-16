<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Response\MockResponse;
use WebPush\Notification;
use WebPush\StatusReport;
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
        $report = StatusReport::createFromResponse($subscription, $notification, $response);

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
    public static function dataReport(): iterable
    {
        yield [199, false, false];
        yield [200, true, false];
        yield [201, true, false];
        yield [202, true, false];
        yield [299, true, false];
        yield [300, false, false];
        yield [301, false, false];
        yield [400, false, false];
        yield [403, false, false];
        yield [404, false, true];
        yield [405, false, false];
        yield [409, false, false];
        yield [410, false, true];
        yield [411, false, false];
    }
}
