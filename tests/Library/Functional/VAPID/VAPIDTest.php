<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Functional\VAPID;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\NativeClock;
use WebPush\Base64Url;
use WebPush\Notification;
use WebPush\RequestData;
use WebPush\Subscription;
use WebPush\VAPID\LcobucciProvider;
use WebPush\VAPID\VAPIDExtension;
use function json_decode;
use const JSON_THROW_ON_ERROR;

/**
 * @internal
 */
final class VAPIDTest extends TestCase
{
    #[Test]
    public function vapidHeaderCanBeAdded(): void
    {
        $jwsProvider = LcobucciProvider::create(
            'BDCgQkzSHClEg4otdckrN-duog2fAIk6O07uijwKr-w-4Etl6SRW2YiLUrN5vfvVHuhp7x8PxltmWWlbbM4IFyM',
            '870MB6gfuTJ4HtUnUvYMyJpr5eUZNP4Bk43bVdj3eAE'
        );

        $notification = Notification::create();
        $subscription = Subscription::create('https://foo.bar:1337/test?a=foo&b=BAR');

        $requestData = new RequestData();
        VAPIDExtension::create('subject', $jwsProvider, new NativeClock())
            ->setTokenExpirationTime('now +2hours')
            ->process($requestData, $notification, $subscription)
        ;

        $vapidHeader = $requestData->getHeaders()['Authorization'];
        static::assertStringStartsWith('vapid t=', $vapidHeader);
        $tokenPayload = mb_substr((string) $vapidHeader, 45);
        $position = mb_strpos($tokenPayload, '.');
        $tokenPayload = mb_substr($tokenPayload, 0, $position === false ? null : $position);
        $tokenPayload = Base64Url::decode($tokenPayload);
        $claims = json_decode($tokenPayload, true, 512, JSON_THROW_ON_ERROR);

        static::assertArrayHasKey('aud', $claims);
        static::assertArrayHasKey('sub', $claims);
        static::assertArrayHasKey('exp', $claims);
        static::assertSame('https://foo.bar:1337', $claims['aud']);
        static::assertSame('subject', $claims['sub']);
        static::assertGreaterThanOrEqual(time(), $claims['exp']);
    }
}
