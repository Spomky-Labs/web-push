<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2020 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace WebPush\Tests\Library\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use WebPush\Notification;
use WebPush\StatusReport;
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
    public function report(int $statusCode, bool $isSuccess): void
    {
        $subscription = self::createMock(Subscription::class);
        $notification = self::createMock(Notification::class);
        $request = self::createMock(RequestInterface::class);
        $response = self::createMock(ResponseInterface::class);
        $response
            ->expects(static::once())
            ->method('getHeaderLine')
            ->with('location')
            ->willReturn('https://foo.bar')
        ;
        $response
            ->expects(static::once())
            ->method('getStatusCode')
            ->willReturn($statusCode)
        ;
        $response
            ->expects(static::once())
            ->method('getHeader')
            ->with('link')
            ->willReturn(['https://link.1'])
        ;
        $report = new StatusReport(
            $subscription,
            $notification,
            $request,
            $response
        );

        static::assertSame($subscription, $report->getSubscription());
        static::assertSame($notification, $report->getNotification());
        static::assertSame($request, $report->getRequest());
        static::assertSame($response, $report->getResponse());
        static::assertEquals('https://foo.bar', $report->getLocation());
        static::assertEquals(['https://link.1'], $report->getLinks());
        static::assertEquals($isSuccess, $report->isSuccess());
    }

    /**
     * @return array[]
     */
    public function dataReport(): array
    {
        return [
            [199, false],
            [200, true],
            [201, true],
            [202, true],
            [299, true],
            [300, false],
            [301, false],
        ];
    }
}
