<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\NativeClock;
use WebPush\Notification;
use WebPush\Payload\AESGCM;
use WebPush\Payload\PayloadExtension;
use WebPush\RequestData;
use WebPush\Subscription;

/**
 * @internal
 */
final class PayloadExtensionTest extends TestCase
{
    #[Test]
    public function canProcessWithoutPayload(): void
    {
        // Given
        $requestData = new RequestData();
        $notification = Notification::create();
        $subscription = Subscription::create('https://foo.bar');

        // When
        PayloadExtension::create()
            ->process($requestData, $notification, $subscription)
        ;

        // Then
        static::assertSame('0', $requestData->getHeaders()['Content-Length']);
    }

    #[Test]
    public function canProcessWithPayload(): void
    {
        // Given
        $notification = Notification::create()
            ->withPayload('Payload')
        ;
        $subscription = Subscription::create('https://foo.bar');
        $subscription->setKey(
            'p256dh',
            'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcx aOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw4'
        );
        $subscription->setKey('auth', 'BTBZMqHH6r4Tts7J_aSIgg');
        $requestData = new RequestData();

        // When
        PayloadExtension::create()
            ->addContentEncoding(AESGCM::create(new NativeClock()))
            ->process($requestData, $notification, $subscription)
        ;

        // Then
        static::assertSame('application/octet-stream', $requestData->getHeaders()['Content-Type']);
        static::assertSame('aesgcm', $requestData->getHeaders()['Content-Encoding']);
    }

    #[Test]
    public function unsupportedContentEncoding(): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(
            'No content encoding found. Supported content encodings for the subscription are: aesgcm'
        );

        $notification = Notification::create()
            ->withPayload('Payload')
        ;
        $subscription = Subscription::create('https://foo.bar');
        $subscription->setKey(
            'p256dh',
            'BCVxsr7N_eNgVRqvHtD0zTZsEc6-VV-JvLexhqUzORcx aOzi6-AYWXvTBHm4bjyPjs7Vd8pZGH6SRpkNtoIAiw4'
        );
        $subscription->setKey('auth', 'BTBZMqHH6r4Tts7J_aSIgg');

        PayloadExtension::create()
            ->process(new RequestData(), $notification, $subscription)
        ;
    }
}
