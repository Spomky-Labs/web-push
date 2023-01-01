<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use InvalidArgumentException;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\NativeClock;
use WebPush\Notification;
use WebPush\Payload\AESGCM;
use WebPush\Payload\PayloadExtension;
use WebPush\Subscription;

/**
 * @internal
 */
final class PayloadExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function canProcessWithoutPayload(): void
    {
        // Given
        $request = new Request('POST', 'https://foo.bar');
        $notification = Notification::create();
        $subscription = Subscription::create('https://foo.bar');

        // When
        $request = PayloadExtension::create()
            ->process($request, $notification, $subscription)
        ;

        // Then
        static::assertSame('0', $request->getHeaderLine('content-length'));
    }

    /**
     * @test
     */
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
        $request = new Request('POST', 'https://foo.bar');

        // When
        $request = PayloadExtension::create()
            ->addContentEncoding(AESGCM::create(new NativeClock()))
            ->process($request, $notification, $subscription)
        ;

        // Then
        static::assertSame('application/octet-stream', $request->getHeaderLine('content-type'));
        static::assertSame('aesgcm', $request->getHeaderLine('content-encoding'));
    }

    /**
     * @test
     */
    public function unsupportedContentEncoding(): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(
            'No content encoding found. Supported content encodings for the subscription are: aesgcm'
        );

        $request = new Request('POST', 'https://foo.bar');

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
            ->process($request, $notification, $subscription)
        ;
    }
}
