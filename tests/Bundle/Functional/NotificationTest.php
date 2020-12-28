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

namespace WebPush\Tests\Bundle\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WebPush\Action;
use WebPush\Message;
use WebPush\Notification;
use WebPush\Subscription;
use WebPush\Tests\Bundle\MockClientCallback;
use WebPush\WebPush;

/**
 * @group functional
 *
 * @internal
 */
class NotificationTest extends KernelTestCase
{
    /**
     * @test
     * @dataProvider listOfSubscriptions
     */
    public function iCanSendNotificationsUsingAESGCMEncryption(string $data): void
    {
        $kernel = self::bootKernel();
        /** @var WebPush $pushService */
        $pushService = $kernel->getContainer()->get(WebPush::class);
        /** @var MockClientCallback $responseFactory */
        $responseFactory = self::$container->get(MockClientCallback::class);
        $responseFactory->setResponse('', [
            'http_code' => 201,
        ]);

        $subscription = Subscription::createFromString($data);

        $message = Message::create('Hello World!')
            ->withLang('en-GB')
            ->interactionRequired()
            ->withTimestamp(time())
            ->addAction(Action::create('accept', 'Accept'))
            ->addAction(Action::create('cancel', 'Cancel'))
        ;

        $notification = Notification::create()
            ->withTTL(10)
            ->withTopic('test')
            ->lowUrgency()
            ->withPayload($message->toString())
        ;

        $report = $pushService->send($notification, $subscription);

        static::assertEquals(201, $report->getResponse()->getStatusCode());

        $request = $report->getRequest();
        static::assertEquals([Notification::URGENCY_LOW], $request->getHeader('urgency'));
        static::assertEquals([10], $request->getHeader('ttl'));
        static::assertEquals(['test'], $request->getHeader('topic'));
        static::assertEquals(['application/octet-stream'], $request->getHeader('content-type'));
        static::assertEquals(['aesgcm'], $request->getHeader('content-encoding'));

        static::assertTrue($request->hasHeader('crypto-key'));
        static::assertTrue($request->hasHeader('encryption'));
        static::assertTrue($request->hasHeader('authorization'));
    }

    /**
     * @test
     * @dataProvider listOfSubscriptions
     */
    public function iCanSendNotificationsUsingAES128GCMEncryption(string $data): void
    {
        $kernel = self::bootKernel();
        /** @var WebPush $pushService */
        $pushService = $kernel->getContainer()->get(WebPush::class);
        /** @var MockClientCallback $responseFactory */
        $responseFactory = self::$container->get(MockClientCallback::class);
        $responseFactory->setResponse('', [
            'http_code' => 201,
        ]);

        $subscription = Subscription::createFromString($data);
        $subscription->withContentEncodings(['aes128gcm']);

        $message = Message::create('Hello World!')
            ->withLang('en-GB')
            ->interactionRequired()
            ->withTimestamp(time())
            ->addAction(Action::create('accept', 'Accept'))
            ->addAction(Action::create('cancel', 'Cancel'))
        ;

        $notification = Notification::create()
            ->withTTL(3600)
            ->withTopic('FOO')
            ->veryLowUrgency()
            ->withPayload($message->toString())
        ;

        $report = $pushService->send($notification, $subscription);

        static::assertEquals(201, $report->getResponse()->getStatusCode());

        $request = $report->getRequest();
        static::assertEquals([Notification::URGENCY_VERY_LOW], $request->getHeader('urgency'));
        static::assertEquals([3600], $request->getHeader('ttl'));
        static::assertEquals(['FOO'], $request->getHeader('topic'));
        static::assertEquals(['application/octet-stream'], $request->getHeader('content-type'));
        static::assertEquals(['aes128gcm'], $request->getHeader('content-encoding'));

        static::assertFalse($request->hasHeader('crypto-key'));
        static::assertFalse($request->hasHeader('encryption'));
        static::assertTrue($request->hasHeader('authorization'));
    }

    public function listOfSubscriptions(): array
    {
        return [
            [
                '{"endpoint":"https:\/\/fcm.googleapis.com\/fcm\/send\/fsTzuK_gGAE:APA91bGOo_qYwoGQoiKt6tM_GX9-jNXU9yGF4stivIeRX4cMZibjiXUAojfR_OfAT36AZ7UgfLbts011308MY7IYUljCxqEKKhwZk0yPjf9XOb-A7usa47gu1t__TfCrvQoXkrTiLuOt","contentEncoding":"aes128gcm","keys":{"p256dh":"BGx19OjV00A00o9DThFSX-q40h6FA3t_UATZLrYvJGHdruyY_6T1ug6gOczcSI2HtjV5NUGZKGmykaucnLuZgY4","auth":"gW9ZePDxvjUILvlYe3Dnug"}}',
            ],
            [
                '{"endpoint":"https:\/\/updates.push.services.mozilla.com\/wpush\/v2\/gAAAAABf28w8IBzfbD-ckKA6G0PNS5NVzRj8Ui6xsXS3XVE8Fn-l89cTjauuUYflfYO_pf4boI1DVlav2VkZg5YSymJ0jfUpfpUkXKbTk7MCLob7oM3CvSP7t1iCDhGUNBxAoB-kULsc3LGNxE8gZJbc-nFXjITaCAgFjIITDFlWIByMOKU2aUw","contentEncoding":"aesgcm","keys":{"auth":"TjVb1npRZ9OtlHrVecRc5w","p256dh":"BHF4I9ntV7K7MBgkAb-sA1L3YYKw5Q1Gynwz52iK8fjl21UOofhAyGJR-7Tded-ZpPKEPvpGHssHCWqethky65A"}}',
            ],
            [
                '{"endpoint":"https:\/\/db5p.notify.windows.com\/w\/?token=BQYAAACFSUYbq4Y62SPVdjevSnRSv2TM13ZLddTavvA8uApznqQ%2fiZn7obbFowZNKB552wmkVdaLh04FcJyWsAJ3iB2wxq2tc66CL19q%2fJrOaKAQ0hwUazGqu0BIFTBVeVOwmiGXhs5xpiX3Zl%2ffzOlmevxROP1dCXDKFPuS1RPwo4VMYRZ4JZY6HCvWRmTsB9kK9YtWsGUU%2bQ32pHzkUnPCBmLGZ70ZrJAp%2f9bsnNA3%2b5EsQIUDosvvvV5q8lNX2aiwKGdemndqtouTVkx4Wm436CH8vK0fJtlMGiH0cbt0RvlfEYN0dBkcSo0gdnx8hPebm8g%3d","contentEncoding":"aes128gcm","keys":{"p256dh":"BIreHXM7-HXqZfiuUfKD4o7QGmFkyp3Yz1EQWqOOZVxs9CdE-_2jay4j3s5syS-z4X54EzHtoM3-xMEOkaT4tEY","auth":"wt7wGPYcijytA8DOH17UhQ"}}',
            ],
        ];
    }
}
