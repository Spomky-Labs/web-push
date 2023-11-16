<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use WebPush\Base64Url;

/**
 * @internal
 */
final class Base64UrlTest extends TestCase
{
    #[Test]
    #[DataProvider('getTestVectors')]
    public function encodeAndDecode(string $message, string $expectedResult): void
    {
        $encoded = Base64Url::encode($message);
        $decoded = Base64Url::decode($expectedResult);

        static::assertSame($expectedResult, $encoded);
        static::assertSame($message, $decoded);
    }

    /**
     * @see https://tools.ietf.org/html/rfc4648#section-10
     *
     * @return array<int, array<int, string>>
     */
    public static function getTestVectors(): iterable
    {
        yield ['000000', 'MDAwMDAw'];
        yield ["\0\0\0\0", 'AAAAAA'];
        yield ["\xff", '_w'];
        yield ["\xff\xff", '__8'];
        yield ["\xff\xff\xff", '____'];
        yield ["\xff\xff\xff\xff", '_____w'];
        yield ["\xfb", '-w'];
        yield ['', ''];
        yield ['f', 'Zg'];
        yield ['fo', 'Zm8'];
    }

    #[Test]
    #[DataProvider('getTestBadVectors')]
    public function badInput(string $input): void
    {
        $decoded = Base64Url::decode($input);
        static::assertSame("\00", $decoded);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function getTestBadVectors(): iterable
    {
        yield [' AA'];
        yield ["\tAA"];
        yield ["\rAA"];
        yield ["\nAA"];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function getTestNonsenseVectors(): array
    {
        return [['cxr0fdsezrewklerewxoz423ocfsa3bw432yjydsa9lhdsalw']];
    }
}
