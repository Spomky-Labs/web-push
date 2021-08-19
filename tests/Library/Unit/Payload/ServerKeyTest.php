<?php

declare(strict_types=1);

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
