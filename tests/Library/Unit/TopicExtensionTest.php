<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use WebPush\Notification;
use WebPush\Subscription;
use WebPush\TopicExtension;

/**
 * @internal
 */
final class TopicExtensionTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataTopicIsSetInHeader
     */
    public function topicIsSetInHeader(?string $topic): void
    {
        $logger = new TestLogger();
        $request = new Request('POST', 'https://foo.bar');

        $notification = Notification::create();
        if ($topic !== '') {
            $notification = $notification->withTopic($topic);
        }

        $subscription = Subscription::create('https://foo.bar');

        $request = TopicExtension::create()
            ->setLogger($logger)
            ->process($request, $notification, $subscription)
        ;

        static::assertSame($topic, $request->getHeaderLine('topic'));
        static::assertCount(1, $logger->records);
        static::assertSame('debug', $logger->records[0]['level']);
        static::assertSame('Processing with the Topic extension', $logger->records[0]['message']);
        if ($topic === '') {
            static::assertNull($logger->records[0]['context']['Topic']);
        } else {
            static::assertSame($topic, $logger->records[0]['context']['Topic']);
        }
    }

    /**
     * @return array<int, array<int, string|null>>
     */
    public function dataTopicIsSetInHeader(): array
    {
        return [[''], ['topic1'], ['foo-bar']];
    }
}
