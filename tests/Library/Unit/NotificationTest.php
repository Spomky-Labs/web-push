<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WebPush\Notification;

/**
 * @internal
 */
final class NotificationTest extends TestCase
{
    /**
     * @test
     */
    public function createNotificationFluent(): void
    {
        $subscription = Notification::create()
            ->veryLowUrgency()
            ->lowUrgency()
            ->normalUrgency()
            ->highUrgency()
            ->withUrgency(Notification::URGENCY_HIGH)
            ->withTTL(0)
            ->withPayload('payload')
            ->withTopic('topic')
            ->sync()
        ;

        static::assertSame(Notification::URGENCY_HIGH, $subscription->getUrgency());
        static::assertSame(0, $subscription->getTTL());
        static::assertSame('payload', $subscription->getPayload());
        static::assertSame('topic', $subscription->getTopic());
        static::assertFalse($subscription->isAsync());
    }

    /**
     * @test
     */
    public function createNotificationWithTTL(): void
    {
        $subscription = Notification::create()
            ->withTTL(3600)
        ;

        static::assertSame(Notification::URGENCY_NORMAL, $subscription->getUrgency());
        static::assertSame(3600, $subscription->getTTL());
        static::assertNull($subscription->getPayload());
        static::assertNull($subscription->getTopic());
    }

    /**
     * @test
     */
    public function createAsyncNotification(): void
    {
        $subscription = Notification::create()
            ->async()
        ;

        static::assertTrue($subscription->isAsync());
    }

    /**
     * @test
     */
    public function defaultNotificationIsSync(): void
    {
        $subscription = Notification::create();

        static::assertFalse($subscription->isAsync());
    }

    /**
     * @test
     * @dataProvider dataUrgencies
     */
    public function urgencies(string $urgency): void
    {
        $subscription = Notification::create()
            ->withUrgency($urgency)
        ;

        static::assertSame($urgency, $subscription->getUrgency());
    }

    /**
     * @test
     */
    public function invalidUrgency(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid urgency parameter');

        Notification::create()
            ->withUrgency('urgency')
        ;
    }

    /**
     * @test
     */
    public function invalidTopic(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid topic');

        Notification::create()
            ->withTopic('')
        ;
    }

    /**
     * @test
     */
    public function invalidTTL(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid TTL');

        Notification::create()
            ->withTTL(-1)
        ;
    }

    /**
     * @test
     */
    public function createNotificationWithMetadata(): void
    {
        $notification = Notification::create()
            ->add('foo', 'BAR')
        ;

        static::assertFalse($notification->has('nope'));
        static::assertTrue($notification->has('foo'));
        static::assertSame('BAR', $notification->get('foo'));
        static::assertSame([
            'foo' => 'BAR',
        ], $notification->getMetadata());
    }

    /**
     * @test
     */
    public function missingMetadata(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Missing metadata');
        $notification = Notification::create();

        $notification->get('fff');
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function dataUrgencies(): array
    {
        return [
            [Notification::URGENCY_VERY_LOW],
            [Notification::URGENCY_LOW],
            [Notification::URGENCY_NORMAL],
            [Notification::URGENCY_HIGH],
        ];
    }
}
