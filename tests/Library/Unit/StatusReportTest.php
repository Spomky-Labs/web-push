<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2020-2021 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace WebPush\Tests\Library\Unit;

use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use WebPush\Notification;
use WebPush\StatusStatusReportInterface;
use WebPush\Subscription;

/**
 * @internal
 * @group Unit
 * @group Library
 */
final class StatusReportTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataReport
     */
    public function report(int $statusCode, bool $isSuccess, bool $hasExpired): void
    {
        $subscription = Subscription::create('https://foo.bar');
        $notification = Notification::create();
        $request = new Request('POST', 'https://foo.bar');
        $response = new Response($statusCode, [
            'location' => ['https://foo.bar'],
            'link' => ['https://link.1'],
        ]);
        $report = new StatusStatusReportInterface(
            $subscription,
            $notification,
            $request,
            $response
        );

        static::assertSame($subscription, $report->getSubscription());
        static::assertSame($notification, $report->getNotification());
        static::assertEquals('https://foo.bar', $report->getLocation());
        static::assertEquals(['https://link.1'], $report->getLinks());
        static::assertEquals($isSuccess, $report->isSuccess());
        static::assertEquals($hasExpired, $report->isSubscriptionExpired());
    }

    /**
     * @return array[]
     */
    public function dataReport(): array
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
