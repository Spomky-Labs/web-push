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
use Psr\Log\LoggerInterface;
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
        $logger = self::createMock(LoggerInterface::class);
        $logger
            ->expects(static::once())
            ->method('debug')
            ->with('Processing with the Topic extension', ['Topic' => $topic])
        ;

        $request = self::createMock(RequestInterface::class);
        if (null === $topic) {
            $request
                ->expects(static::never())
                ->method(static::anything())
                ->willReturnSelf()
            ;
        } else {
            $request
                ->expects(static::once())
                ->method('withHeader')
                ->with('Topic', $topic)
                ->willReturnSelf()
            ;
        }

        $notification = self::createMock(Notification::class);
        $notification
            ->expects(static::once())
            ->method('getTopic')
            ->willReturn($topic)
        ;
        $subscription = self::createMock(Subscription::class);

        TopicExtension::create()
            ->setLogger($logger)
            ->process($request, $notification, $subscription)
        ;
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
