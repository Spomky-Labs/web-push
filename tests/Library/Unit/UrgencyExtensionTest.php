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
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use WebPush\Notification;
use WebPush\Subscription;
use WebPush\UrgencyExtension;

/**
 * @internal
 * @group Unit
 * @group Library
 */
final class UrgencyExtensionTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataUrgencyIsSetInHeader
     */
    public function urgencyIsSetInHeader(string $urgency): void
    {
        $logger = new TestLogger();
        $request = new Request('POST', 'https://foo.bar');

        $notification = Notification::create()
            ->withUrgency($urgency)
        ;
        $subscription = Subscription::create('https://foo.bar');

        $request = UrgencyExtension::create()
            ->setLogger($logger)
            ->process($request, $notification, $subscription)
        ;

        static::assertEquals($urgency, $request->getHeaderLine('urgency'));
        static::assertCount(1, $logger->records);
        static::assertEquals('debug', $logger->records[0]['level']);
        static::assertEquals('Processing with the Urgency extension', $logger->records[0]['message']);
        static::assertEquals($urgency, $logger->records[0]['context']['Urgency']);
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
