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

use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use WebPush\Notification;
use WebPush\Subscription;
use WebPush\TopicExtension;

/**
 * @internal
 * @group Unit
 * @group Library
 */
final class TopicExtensionTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataTopicIsSetInHeader
     */
    public function topicIsSetInHeader(?string $topic): void
    {
        $logger = new TestLogger();
        $request = new Request('POST', 'https://foo.bar');

        $notification = Notification::create();
        if (null !== $topic) {
            $notification = $notification->withTopic($topic);
        }

        $subscription = Subscription::create('https://foo.bar');

        $request = TopicExtension::create()
            ->setLogger($logger)
            ->process($request, $notification, $subscription)
        ;

        static::assertEquals($topic, $request->getHeaderLine('topic'));
        static::assertCount(1, $logger->records);
        static::assertEquals('debug', $logger->records[0]['level']);
        static::assertEquals('Processing with the Topic extension', $logger->records[0]['message']);
        static::assertEquals($topic, $logger->records[0]['context']['Topic']);
    }

    /**
     * @return array<int, array<int, string|null>>
     */
    public function dataTopicIsSetInHeader(): array
    {
        return [
            [null],
            ['topic1'],
            ['foo-bar'],
        ];
    }
}
