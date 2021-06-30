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

namespace WebPush\Tests\Bundle\Functional;

use Nyholm\Psr7\Request;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WebPush\Extension;
use WebPush\ExtensionManager;
use WebPush\Notification;
use WebPush\Payload\PayloadExtension;
use WebPush\PreferAsyncExtension;
use WebPush\Subscription;
use WebPush\TopicExtension;
use WebPush\TTLExtension;
use WebPush\UrgencyExtension;
use WebPush\VAPID\VAPIDExtension;

/**
 * @group functional
 *
 * @internal
 */
class ExtensionTest extends KernelTestCase
{
    /**
     * @test
     * @dataProvider  listOfPayloadExtensions
     *
     * @param mixed $class
     */
    public function taggedExtensionsAreAutoConfigured($class): void
    {
        self::bootKernel();
        $service = self::getContainer()->get($class);
        static::assertInstanceOf(Extension::class, $service);
    }

    /**
     * @test
     */
    public function extensionManagerHasAllExtensions(): void
    {
        self::bootKernel();
        /** @var ExtensionManager $service */
        $service = self::getContainer()->get(ExtensionManager::class);

        $data = '{"endpoint":"https:\/\/fcm.googleapis.com\/fcm\/send\/fsTzuK_gGAE:APA91bGOo_qYwoGQoiKt6tM_GX9-jNXU9yGF4stivIeRX4cMZibjiXUAojfR_OfAT36AZ7UgfLbts011308MY7IYUljCxqEKKhwZk0yPjf9XOb-A7usa47gu1t__TfCrvQoXkrTiLuOt","contentEncoding":"aes128gcm","keys":{"p256dh":"BGx19OjV00A00o9DThFSX-q40h6FA3t_UATZLrYvJGHdruyY_6T1ug6gOczcSI2HtjV5NUGZKGmykaucnLuZgY4","auth":"gW9ZePDxvjUILvlYe3Dnug"}}';
        $subscription = Subscription::createFromString($data);
        $notification = Notification::create()
            ->async()
            ->withTopic('topic')
            ->withTTL(1337)
            ->highUrgency()
            ->withPayload('PAYLOAD')
        ;
        $request = new Request('POST', 'https://www.foo.bar:1337/test?a=FOO');

        $request = $service->process($request, $notification, $subscription);

        static::assertEquals('respond-async', $request->getHeaderLine('Prefer')); //Async
        static::assertEquals('topic', $request->getHeaderLine('Topic')); //Topic
        static::assertEquals('1337', $request->getHeaderLine('TTL')); // TTL
        static::assertEquals('high', $request->getHeaderLine('Urgency')); //Urgency

        static::assertEquals('aesgcm', $request->getHeaderLine('Content-Encoding')); //Payload encryption
        static::assertStringStartsWith('dh=', $request->getHeaderLine('Crypto-Key')); //Payload encryption
        static::assertStringStartsWith('salt=', $request->getHeaderLine('Encryption')); //Payload encryption
        static::assertGreaterThanOrEqual(3070, (int) $request->getHeaderLine('Content-Length')); //Payload encryption

        static::assertStringStartsWith('vapid t=eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NiJ9.', $request->getHeaderLine('Authorization')); //VAPID
    }

    public function listOfPayloadExtensions(): array
    {
        return [
            [
                PreferAsyncExtension::class,
            ],
            [
                TopicExtension::class,
            ],
            [
                TTLExtension::class,
            ],
            [
                UrgencyExtension::class,
            ],
            [
                PayloadExtension::class,
            ],
            [
                VAPIDExtension::class,
            ],
        ];
    }
}
