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

namespace WebPush\Tests\Library\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function Safe\json_encode;
use WebPush\Keys;

/**
 * @internal
 * @group Unit
 * @group Library
 */
final class KeysTest extends TestCase
{
    /**
     * @test
     */
    public function keysAreCorrectlyManaged(): void
    {
        $keys = new Keys();
        $keys
            ->set('foo', 'BAR')
        ;

        static::assertTrue($keys->has('foo'));
        static::assertEquals('BAR', $keys->get('foo'));
        static::assertEquals(['foo' => 'BAR'], $keys->all());
        static::assertEquals(['foo'], $keys->list());
        static::assertEquals('{"foo":"BAR"}', json_encode($keys));
        static::assertEquals($keys, Keys::createFromAssociativeArray(['foo' => 'BAR']));
    }

    /**
     * @test
     */
    public function cannotGetAnUndefinedKey(): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Undefined key name "foo"');

        $keys = new Keys();
        $keys->get('foo');
    }
}
