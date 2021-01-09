<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2020-2021 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace WebPush\Tests\Library\Unit\Payload;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WebPush\Payload\ServerKey;

/**
 * @internal
 * @group Unit
 * @group Library
 */
final class ServerKeyTest extends TestCase
{
    /**
     * @test
     */
    public function invalidPublicKeyLength(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Invalid public key length');

        new ServerKey('', '');
    }

    /**
     * @test
     */
    public function invalidPrivateKeyLength(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Invalid private key length');

        $fakePublicKey = str_pad('', 65, '-');

        new ServerKey($fakePublicKey, '');
    }
}
