<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use WebPush\Notification;
use WebPush\RequestData;
use WebPush\Subscription;
use WebPush\TopicExtension;

/**
 * @internal
 */
final class TopicExtensionTest extends TestCase
{
    #[Test]
    #[DataProvider('dataTopicIsSetInHeader')]
    public function topicIsSetInHeader(?string $topic): void
    {
        // Given
        $requestData = new RequestData();

        $notification = Notification::create();
        if ($topic !== null) {
            $notification = $notification->withTopic($topic);
        }

        $subscription = Subscription::create('https://foo.bar');

        // When
        TopicExtension::create()
            ->process($requestData, $notification, $subscription)
        ;

        // Then
        static::assertSame($topic, $requestData->getHeaders()['Topic']);
    }

    /**
     * @return array<int, array<int, string|null>>
     */
    public static function dataTopicIsSetInHeader(): array
    {
        return [[null], ['topic1'], ['foo-bar']];
    }
}
