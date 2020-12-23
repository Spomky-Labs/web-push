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

namespace WebPush\Tests\Library\Functional\VAPID;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use function Safe\json_decode;
use WebPush\Base64Url;
use WebPush\Notification;
use WebPush\Subscription;
use WebPush\VAPID\LcobucciProvider;
use WebPush\VAPID\VAPIDExtension;

/**
 * @internal
 * @group Functional
 * @group Library
 */
final class VAPIDTest extends TestCase
{
    /**
     * @test
     */
    public function vapidHeaderCanBeAdded(): void
    {
        $jwsProvider = LcobucciProvider::create(
            'BDCgQkzSHClEg4otdckrN-duog2fAIk6O07uijwKr-w-4Etl6SRW2YiLUrN5vfvVHuhp7x8PxltmWWlbbM4IFyM',
            '870MB6gfuTJ4HtUnUvYMyJpr5eUZNP4Bk43bVdj3eAE'
        );

        $logger = new TestLogger();
        $request = new Request('POST', 'https://foo.bar');

        $notification = Notification::create();
        $subscription = Subscription::create('https://foo.bar');

        $request = VAPIDExtension::create('subject', $jwsProvider)
            ->setLogger($logger)
            ->setTokenExpirationTime('now +2hours')
            ->process($request, $notification, $subscription)
        ;

        $vapidHeader = $request->getHeaderLine('authorization');
        static::assertStringStartsWith('vapid t=', $vapidHeader);
        $tokenPayload = mb_substr($vapidHeader, 45);
        $position = mb_strpos($tokenPayload, '.');
        $tokenPayload = mb_substr($tokenPayload, 0, $position === false ? null : $position);
        $tokenPayload = Base64Url::decode($tokenPayload);
        $claims = json_decode($tokenPayload, true);

        static::assertArrayHasKey('aud', $claims);
        static::assertArrayHasKey('sub', $claims);
        static::assertArrayHasKey('exp', $claims);
        static::assertEquals('https://foo.bar', $claims['aud']);
        static::assertEquals('subject', $claims['sub']);
        static::assertGreaterThanOrEqual(time(), $claims['exp']);

        static::assertCount(3, $logger->records);
        static::assertEquals('debug', $logger->records[0]['level']);
        static::assertEquals('Processing with VAPID header', $logger->records[0]['message']);
        static::assertEquals('debug', $logger->records[1]['level']);
        static::assertEquals('Trying to get the header from the cache', $logger->records[1]['message']);
        static::assertEquals('debug', $logger->records[2]['level']);
        static::assertEquals('Header from cache', $logger->records[2]['message']);
    }
}
