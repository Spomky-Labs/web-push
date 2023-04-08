<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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
 * @internal
 */
final class ExtensionTest extends KernelTestCase
{
    #[Test]
    #[DataProvider('listOfPayloadExtensions')]
    public function taggedExtensionsAreAutoConfigured($class): void
    {
        self::bootKernel();
        $service = self::getContainer()->get($class);
        static::assertInstanceOf(Extension::class, $service);
    }

    #[Test]
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

        $requestData = $service->process($notification, $subscription);

        static::assertSame('respond-async', $requestData->getHeaders()['Prefer']);
        static::assertSame('topic', $requestData->getHeaders()['Topic']);
        static::assertSame('1337', $requestData->getHeaders()['TTL']);
        static::assertSame('high', $requestData->getHeaders()['Urgency']);
        static::assertSame('aesgcm', $requestData->getHeaders()['Content-Encoding']);
        static::assertGreaterThanOrEqual(3070, $requestData->getHeaders()['Content-Length']);
    }

    public static function listOfPayloadExtensions(): array
    {
        return [
            [PreferAsyncExtension::class],
            [TopicExtension::class],
            [TTLExtension::class],
            [UrgencyExtension::class],
            [PayloadExtension::class],
            [VAPIDExtension::class],
        ];
    }
}
