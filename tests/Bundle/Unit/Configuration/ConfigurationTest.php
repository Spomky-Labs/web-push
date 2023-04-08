<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\Unit\Configuration;

use WebPush\Base64Url;

/**
 * @internal
 */
final class ConfigurationTest extends AbstractConfigurationTestCase
{
    public static function multipleJwsProvider(): array
    {
        return [
            [[
                'web-token' => [
                    'enabled' => true,
                    'private_key' => Base64Url::encode('00000000000000000000000000000000'),
                    'public_key' => Base64Url::encode(
                        '00000000000000000000000000000000000000000000000000000000000000000'
                    ),
                ],
                'lcobucci' => [
                    'enabled' => true,
                    'private_key' => Base64Url::encode('00000000000000000000000000000000'),
                    'public_key' => Base64Url::encode(
                        '00000000000000000000000000000000000000000000000000000000000000000'
                    ),
                ],
            ]],
            [[
                'lcobucci' => [
                    'enabled' => true,
                    'private_key' => Base64Url::encode('00000000000000000000000000000000'),
                    'public_key' => Base64Url::encode(
                        '00000000000000000000000000000000000000000000000000000000000000000'
                    ),
                ],
                'custom' => [
                    'enabled' => true,
                    'id' => 'app.service.foo',
                ],
            ]],
            [[
                'web-token' => [
                    'enabled' => true,
                    'private_key' => Base64Url::encode('00000000000000000000000000000000'),
                    'public_key' => Base64Url::encode(
                        '00000000000000000000000000000000000000000000000000000000000000000'
                    ),
                ],
                'custom' => [
                    'enabled' => true,
                    'id' => 'app.service.foo',
                ],
            ]],
            [[
                'web-token' => [
                    'enabled' => true,
                    'private_key' => Base64Url::encode('00000000000000000000000000000000'),
                    'public_key' => Base64Url::encode(
                        '00000000000000000000000000000000000000000000000000000000000000000'
                    ),
                ],
                'lcobucci' => [
                    'enabled' => true,
                    'private_key' => Base64Url::encode('00000000000000000000000000000000'),
                    'public_key' => Base64Url::encode(
                        '00000000000000000000000000000000000000000000000000000000000000000'
                    ),
                ],
                'custom' => [
                    'enabled' => true,
                    'id' => 'app.service.foo',
                ],
            ]],
        ];
    }
}
