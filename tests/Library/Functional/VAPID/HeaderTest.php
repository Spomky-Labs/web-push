<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2020 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

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
