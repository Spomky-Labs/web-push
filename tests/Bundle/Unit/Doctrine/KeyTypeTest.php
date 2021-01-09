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

namespace WebPush\Tests\Bundle\Unit\Doctrine;

use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Types\ConversionException;
use PHPUnit\Framework\TestCase;
use WebPush\Bundle\Doctrine\Type\KeysType;
use WebPush\Keys;

/**
 * @group unit
 *
 * @internal
 */
class KeyTypeTest extends TestCase
{
    /**
     * @test
     * @dataProvider validDatabaseValues
     *
     * @param mixed $keys
     * @param mixed $expected
     */
    public function theDataIsCorrectlyConvertedToDatabaseValue($keys, $expected): void
    {
        $type = new KeysType();
        $result = $type->convertToDatabaseValue($keys, new SqlitePlatform());

        static::assertEquals($expected, $result);
    }

    /**
     * @test
     * @dataProvider invalidDatabaseValues
     *
     * @param mixed $data
     */
    public function theInvalidDataIsNotConvertedToDatabaseValue($data): void
    {
        static::expectException(ConversionException::class);

        $type = new KeysType();
        $type->convertToDatabaseValue($data, new SqlitePlatform());
    }

    /**
     * @test
     * @dataProvider validDatabaseValues
     *
     * @param mixed $expected
     * @param mixed $data
     */
    public function theDataIsCorrectlyConvertedToPhpValue($expected, $data): void
    {
        $type = new KeysType();
        $result = $type->convertToPHPValue($data, new SqlitePlatform());

        static::assertEquals($expected, $result);
    }

    /**
     * @test
     * @dataProvider invalidDatabaseValues
     *
     * @param mixed $data
     */
    public function theInvalidDataIsNotConvertedToPhpValue($data): void
    {
        static::expectException(ConversionException::class);

        $type = new KeysType();
        $type->convertToPHPValue($data, new SqlitePlatform());
    }

    public function validDatabaseValues(): array
    {
        return [
            [null, null],
            [(new Keys())->set('foo', 'BAR'), '{"foo":"BAR"}'],
        ];
    }

    public function invalidDatabaseValues(): array
    {
        return [
            [123],
            [true],
            [12331.1546],
        ];
    }
}
