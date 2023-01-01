<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
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
        // Given
        $request = new Request('POST', 'https://foo.bar');

        $notification = Notification::create();
        if ($topic !== '') {
            $notification = $notification->withTopic($topic);
        }

        $subscription = Subscription::create('https://foo.bar');

        // When
        $request = TopicExtension::create()
            ->process($request, $notification, $subscription)
        ;

        // Then
        static::assertSame($topic, $request->getHeaderLine('topic'));
    }

    /**
     * @return array<int, array<int, string|null>>
     */
    public function dataTopicIsSetInHeader(): array
    {
        return [[''], ['topic1'], ['foo-bar']];
    }
}
