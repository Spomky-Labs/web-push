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

namespace WebPush\Tests\Library\Unit;

use PHPUnit\Framework\TestCase;
use WebPush\Base64Url;

/**
 * @internal
 * @group Unit
 * @group Library
 */
class Base64UrlTest extends TestCase
{
    /**
     * @dataProvider getTestVectors
     *
     * @test
     */
    public function encodeAndDecode(string $message, string $expectedResult): void
    {
        $encoded = Base64Url::encode($message);
        $decoded = Base64Url::decode($expectedResult);

        static::assertEquals($expectedResult, $encoded);
        static::assertEquals($message, $decoded);
    }

    /**
     * @see https://tools.ietf.org/html/rfc4648#section-10
     *
     * @return array<int, array<int, string>>
     */
    public function getTestVectors(): array
    {
        return [
            [
                '000000', 'MDAwMDAw',
            ],
            [
                "\0\0\0\0", 'AAAAAA',
            ],
            [
                "\xff", '_w',
            ],
            [
                "\xff\xff", '__8',
            ],
            [
                "\xff\xff\xff", '____',
            ],
            [
                "\xff\xff\xff\xff", '_____w',
            ],
            [
                "\xfb", '-w',
            ],
            [
                '', '',
            ],
            [
                'f', 'Zg',
            ],
            [
                'fo', 'Zm8',
            ],
        ];
    }

    /**
     * @dataProvider getTestBadVectors
     *
     * @test
     */
    public function badInput(string $input): void
    {
        $decoded = Base64Url::decode($input);
        static::assertEquals("\00", $decoded);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function getTestBadVectors(): array
    {
        return [
            [
                ' AA',
            ],
            [
                "\tAA",
            ],
            [
                "\rAA",
            ],
            [
                "\nAA",
            ],
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function getTestNonsenseVectors(): array
    {
        return [
            [
                'cxr0fdsezrewklerewxoz423ocfsa3bw432yjydsa9lhdsalw',
            ],
        ];
    }
}
