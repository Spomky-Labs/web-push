<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Functional\VAPID;

use PHPUnit\Framework\TestCase;
use WebPush\VAPID\Header;

/**
 * @internal
 * @group Functional
 * @group Library
 */
final class HeaderTest extends TestCase
{
    /**
     * @test
     */
    public function createHeader(): void
    {
        $header = new Header('token', 'key');

        static::assertEquals('token', $header->getToken());
        static::assertEquals('key', $header->getKey());
    }
}
