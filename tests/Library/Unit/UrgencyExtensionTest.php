<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use WebPush\Notification;
use WebPush\Subscription;
use WebPush\UrgencyExtension;

/**
 * @internal
 */
final class UrgencyExtensionTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataUrgencyIsSetInHeader
     */
    public function urgencyIsSetInHeader(string $urgency): void
    {
        $logger = new TestLogger();
        $request = new Request('POST', 'https://foo.bar');

        $notification = Notification::create()
            ->withUrgency($urgency)
        ;
        $subscription = Subscription::create('https://foo.bar');

        $request = UrgencyExtension::create()
            ->setLogger($logger)
            ->process($request, $notification, $subscription)
        ;

        static::assertSame($urgency, $request->getHeaderLine('urgency'));
        static::assertCount(1, $logger->records);
        static::assertSame('debug', $logger->records[0]['level']);
        static::assertSame('Processing with the Urgency extension', $logger->records[0]['message']);
        static::assertSame($urgency, $logger->records[0]['context']['Urgency']);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function dataUrgencyIsSetInHeader(): array
    {
        return [
            [Notification::URGENCY_VERY_LOW],
            [Notification::URGENCY_LOW],
            [Notification::URGENCY_NORMAL],
            [Notification::URGENCY_HIGH],
        ];
    }
}
