<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit\Payload;

use PHPUnit\Framework\TestCase;
use WebPush\Exception\OperationException;
use WebPush\Payload\ServerKey;

/**
 * @internal
 */
final class ServerKeyTest extends TestCase
{
    /**
     * @test
     */
    public function invalidPublicKeyLength(): void
    {
        static::expectException(OperationException::class);
        static::expectExceptionMessage('Invalid public key length');

        ServerKey::create('', '');
    }

    /**
     * @test
     */
    public function invalidPrivateKeyLength(): void
    {
        static::expectException(OperationException::class);
        static::expectExceptionMessage('Invalid private key length');

        $fakePublicKey = str_pad('', 65, '-');

        ServerKey::create($fakePublicKey, '');
    }
}
