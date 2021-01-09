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
use WebPush\ExtensionManager;
use WebPush\Notification;
use WebPush\PreferAsyncExtension;
use WebPush\Subscription;
use WebPush\TTLExtension;

/**
 * @internal
 * @group Unit
 * @group Library
 */
final class ExtensionManagerTest extends TestCase
{
    /**
     * @test
     */
    public function topicIsSetInHeader(): void
    {
        $logger = new TestLogger();
        $request = new Request('POST', 'https://foo.bar');
        $notification = Notification::create();
        $subscription = Subscription::create('https://foo.bar');

        ExtensionManager::create()
            ->setLogger($logger)
            ->add(new TTLExtension())
            ->add(new PreferAsyncExtension())
            ->process($request, $notification, $subscription)
        ;

        static::assertCount(4, $logger->records);
        static::assertEquals('debug', $logger->records[0]['level']);
        static::assertEquals('Extension added', $logger->records[0]['message']);
        static::assertEquals('debug', $logger->records[1]['level']);
        static::assertEquals('Extension added', $logger->records[1]['message']);
        static::assertEquals('debug', $logger->records[2]['level']);
        static::assertEquals('Processing the request', $logger->records[2]['message']);
        static::assertEquals('debug', $logger->records[3]['level']);
        static::assertEquals('Processing done', $logger->records[3]['message']);
    }
}
