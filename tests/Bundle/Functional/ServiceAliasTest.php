<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\Functional;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WebPush\Tests\WebPushServiceConsumer;
use WebPush\WebPush;
use WebPush\WebPushService;

/**
 * @internal
 */
final class ServiceAliasTest extends KernelTestCase
{
    #[Test]
    public function theWebPushServiceInterfaceCanBeAutowired(): void
    {
        self::bootKernel();

        /** @var WebPushServiceConsumer $consumer */
        $consumer = self::getContainer()->get(WebPushServiceConsumer::class);

        static::assertInstanceOf(WebPush::class, $consumer->getWebPushService());
    }

    #[Test]
    public function theWebPushServiceInterfaceIsAliasedToTheLibraryImplementation(): void
    {
        self::bootKernel();

        static::assertInstanceOf(WebPush::class, self::getContainer()->get(WebPushService::class));
    }
}
