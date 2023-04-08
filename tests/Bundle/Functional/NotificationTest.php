<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WebPush\Action;
use WebPush\Message;
use WebPush\Notification;
use WebPush\Subscription;
use WebPush\Tests\MockClientCallback;
use WebPush\WebPush;
use WebPush\WebPushService;

/**
 * @internal
 */
final class NotificationTest extends KernelTestCase
{
    #[Test]
    #[DataProvider('listOfSubscriptions')]
    public function iCanSendNotificationsWithAESGCMEncryptionAndTheNewService(string $data): void
    {
        $kernel = self::bootKernel();
        /** @var WebPush $pushService */
        $pushService = $kernel->getContainer()
            ->get(WebPush::class)
        ;
        /** @var MockClientCallback $responseFactory */
        $responseFactory = self::getContainer()->get(MockClientCallback::class);
        $responseFactory->setResponse('', [
            'http_code' => 201,
        ]);

        $subscription = Subscription::createFromString($data);
        $subscription->withContentEncodings(['aesgcm']);

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

        static::assertSame($subscription, $report->getSubscription());
        static::assertSame($notification, $report->getNotification());
        static::assertTrue($report->isSuccess());
        static::assertFalse($report->isSubscriptionExpired());
        static::assertSame('', $report->getLocation());
        static::assertSame([], $report->getLinks());
    }

    #[Test]
    #[DataProvider('listOfSubscriptions')]
    public function iCanSendNotificationsWithAES125GCMEncryptionAndTheNewService(string $data): void
    {
        $kernel = self::bootKernel();
        /** @var WebPush $pushService */
        $pushService = $kernel->getContainer()
            ->get(WebPush::class)
        ;
        /** @var MockClientCallback $responseFactory */
        $responseFactory = self::getContainer()->get(MockClientCallback::class);
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
            ->withTTL(10)
            ->withTopic('test')
            ->lowUrgency()
            ->withPayload($message->toString())
        ;

        $report = $pushService->send($notification, $subscription);

        static::assertSame($subscription, $report->getSubscription());
        static::assertSame($notification, $report->getNotification());
        static::assertTrue($report->isSuccess());
        static::assertFalse($report->isSubscriptionExpired());
        static::assertSame('', $report->getLocation());
        static::assertSame([], $report->getLinks());
    }

    #[Test]
    #[DataProvider('listOfSubscriptions')]
    public function iCanSendNotificationsUsingAESGCMEncryption(string $data): void
    {
        $kernel = self::bootKernel();
        /** @var WebPushService $pushService */
        $pushService = $kernel->getContainer()
            ->get('web_push.service')
        ;
        /** @var MockClientCallback $responseFactory */
        $responseFactory = self::getContainer()->get(MockClientCallback::class);
        $responseFactory->setResponse('', [
            'http_code' => 201,
        ]);

        $subscription = Subscription::createFromString($data);
        $subscription->withContentEncodings(['aesgcm']);

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

        static::assertSame($subscription, $report->getSubscription());
        static::assertSame($notification, $report->getNotification());
        static::assertTrue($report->isSuccess());
        static::assertFalse($report->isSubscriptionExpired());
        static::assertSame('', $report->getLocation());
        static::assertSame([], $report->getLinks());
    }

    #[Test]
    #[DataProvider('listOfSubscriptions')]
    public function iCanSendNotificationsUsingAES128GCMEncryption(string $data): void
    {
        $kernel = self::bootKernel();
        /** @var WebPushService $pushService */
        $pushService = $kernel->getContainer()
            ->get('web_push.service')
        ;
        /** @var MockClientCallback $responseFactory */
        $responseFactory = self::getContainer()->get(MockClientCallback::class);
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

        static::assertSame($subscription, $report->getSubscription());
        static::assertSame($notification, $report->getNotification());
        static::assertTrue($report->isSuccess());
        static::assertFalse($report->isSubscriptionExpired());
        static::assertSame('', $report->getLocation());
        static::assertSame([], $report->getLinks());
    }

    public static function listOfSubscriptions(): array
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
