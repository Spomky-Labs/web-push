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

use InvalidArgumentException;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use WebPush\Notification;
use WebPush\Payload\AESGCM;
use WebPush\Payload\PayloadExtension;
use WebPush\Subscription;

/**
 * @internal
 * @group Unit
 * @group Library
 */
final class PayloadExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function canProcessWithoutPayload(): void
    {
        $logger = new TestLogger();
        $request = new Request('POST', 'https://foo.bar');
        $notification = Notification::create();
        $subscription = Subscription::create('https://foo.bar');

        $request = PayloadExtension::create()
            ->setLogger($logger)
            ->process($request, $notification, $subscription)
        ;

        static::assertEquals('0', $request->getHeaderLine('content-length'));

        static::assertCount(2, $logger->records);
        static::assertEquals('debug', $logger->records[0]['level']);
        static::assertEquals('Processing with payload', $logger->records[0]['message']);
        static::assertEquals('debug', $logger->records[1]['level']);
        static::assertEquals('No payload', $logger->records[1]['message']);
    }

    /**
     * @test
     */
    public function canProcessWithPayload(): void
    {
        $logger = new TestLogger();
        $notification = Notification::create()
            ->withPayload('Payload')
        ;
        $subscription = Subscription::create('https://foo.bar');
        $subscription->getKeys()->set('p256dh', 'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcx aOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw4');
        $subscription->getKeys()->set('auth', 'BTBZMqHH6r4Tts7J_aSIgg');

        $request = new Request('POST', 'https://foo.bar');

        $request = PayloadExtension::create()
            ->setLogger($logger)
            ->addContentEncoding(AESGCM::create())
            ->process($request, $notification, $subscription)
        ;

        static::assertEquals('application/octet-stream', $request->getHeaderLine('content-type'));
        static::assertEquals('aesgcm', $request->getHeaderLine('content-encoding'));

        static::assertCount(2, $logger->records);
        static::assertEquals('debug', $logger->records[0]['level']);
        static::assertEquals('Processing with payload', $logger->records[0]['message']);
        static::assertEquals('debug', $logger->records[1]['level']);
        static::assertEquals('Encoder found: aesgcm. Processing with the encoder.', $logger->records[1]['message']);
    }

    /**
     * @test
     */
    public function unsupportedContentEncoding(): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('No content encoding found. Supported content encodings for the subscription are: aesgcm');

        $request = new Request('POST', 'https://foo.bar');

        $notification = Notification::create()
            ->withPayload('Payload')
        ;
        $subscription = Subscription::create('https://foo.bar');
        $subscription->getKeys()->set('p256dh', 'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcx aOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw4');
        $subscription->getKeys()->set('auth', 'BTBZMqHH6r4Tts7J_aSIgg');

        PayloadExtension::create()
            ->process($request, $notification, $subscription)
        ;
    }
}
