<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use WebPush\Exception\InvalidTopicException;
use WebPush\Exception\InvalidTTLException;
use WebPush\Exception\InvalidUrgencyException;
use WebPush\Exception\OperationException;
use WebPush\Notification;

/**
 * @internal
 */
final class NotificationTest extends TestCase
{
    #[Test]
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

    #[Test]
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

    #[Test]
    public function createAsyncNotification(): void
    {
        $subscription = Notification::create()
            ->async()
        ;

        static::assertTrue($subscription->isAsync());
    }

    #[Test]
    public function defaultNotificationIsSync(): void
    {
        $subscription = Notification::create();

        static::assertFalse($subscription->isAsync());
    }

    #[Test]
    #[DataProvider('dataUrgencies')]
    public function urgencies(string $urgency): void
    {
        $subscription = Notification::create()
            ->withUrgency($urgency)
        ;

        static::assertSame($urgency, $subscription->getUrgency());
    }

    #[Test]
    public function invalidUrgency(): void
    {
        $this->expectException(InvalidUrgencyException::class);
        $this->expectExceptionMessage('Invalid urgency parameter');

        Notification::create()
            ->withUrgency('urgency')
        ;
    }

    #[Test]
    public function invalidTopic(): void
    {
        $this->expectException(InvalidTopicException::class);
        $this->expectExceptionMessage('Topic cannot be empty');

        Notification::create()
            ->withTopic('')
        ;
    }

    #[Test]
    public function topicTooLong(): void
    {
        $this->expectException(InvalidTopicException::class);
        $this->expectExceptionMessage('Topic exceeds maximum length of 32 characters');

        Notification::create()
            ->withTopic(str_repeat('a', 33))
        ;
    }

    #[Test]
    public function topicWithInvalidCharacters(): void
    {
        $this->expectException(InvalidTopicException::class);
        $this->expectExceptionMessage('Topic must contain only URL-safe characters');

        Notification::create()
            ->withTopic('invalid@topic')
        ;
    }

    #[Test]
    public function topicWithSpaces(): void
    {
        $this->expectException(InvalidTopicException::class);
        $this->expectExceptionMessage('Topic must contain only URL-safe characters');

        Notification::create()
            ->withTopic('invalid topic')
        ;
    }

    #[Test]
    public function topicExactly32Characters(): void
    {
        $topic = str_repeat('a', 32);
        $notification = Notification::create()->withTopic($topic);

        static::assertSame($topic, $notification->getTopic());
    }

    #[Test]
    public function topicWithAllValidCharacters(): void
    {
        $notification = Notification::create()
            ->withTopic('valid-topic_123.test~ABC')
        ;

        static::assertSame('valid-topic_123.test~ABC', $notification->getTopic());
    }

    #[Test]
    public function invalidTTL(): void
    {
        $this->expectException(InvalidTTLException::class);
        $this->expectExceptionMessage('Invalid TTL');

        Notification::create()
            ->withTTL(-1)
        ;
    }

    #[Test]
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

    #[Test]
    public function missingMetadata(): void
    {
        static::expectException(OperationException::class);
        static::expectExceptionMessage('Missing metadata');
        $notification = Notification::create();

        $notification->get('fff');
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function dataUrgencies(): iterable
    {
        yield [Notification::URGENCY_VERY_LOW];
        yield [Notification::URGENCY_LOW];
        yield [Notification::URGENCY_NORMAL];
        yield [Notification::URGENCY_HIGH];
    }
}
