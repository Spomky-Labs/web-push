<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\Unit\Configuration;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use WebPush\Base64Url;

/**
 * @internal
 */
final class ConfigurationTest extends AbstractConfigurationTest
{
    /**
     * @test
     */
    public function noRequiredValueByDefault(): void
    {
        $this->assertProcessedConfigurationEquals(
            [],
            [
                'logger' => null,
                'http_client' => ClientInterface::class,
                'request_factory' => RequestFactoryInterface::class,
                'vapid' => [
                    'enabled' => false,
                    'token_lifetime' => 'now +1hour',
                    'web_token' => [
                        'enabled' => false,
                    ],
                    'lcobucci' => [
                        'enabled' => false,
                    ],
                    'custom' => [
                        'enabled' => false,
                    ],
                ],
                'payload' => [
                    'aes128gcm' => [
                        'padding' => 'recommended',
                        'cache' => null,
                        'cache_lifetime' => 'now + 30min',
                    ],
                    'aesgcm' => [
                        'padding' => 'recommended',
                        'cache' => null,
                        'cache_lifetime' => 'now + 30min',
                    ],
                ],
            ]
        );
    }

    /**
     * @test
     */
    public function invalidIfNoSubjectIsSetWhenVapidIsEnabled(): void
    {
        $this->assertConfigurationIsInvalid(
            [
                [
                    'vapid' => [
                        'enabled' => true,
                    ],
                ],
            ],
            'The child config "subject" under "webpush.vapid" must be configured: The URL of the service or an email address'
        );
    }

    /**
     * @test
     */
    public function invalidIfNoJwtProviderIsEnabled(): void
    {
        $this->assertConfigurationIsInvalid(
            [
                [
                    'vapid' => [
                        'enabled' => true,
                        'subject' => 'https://foo.bar',
                    ],
                ],
            ],
            'Invalid configuration for path "webpush.vapid": One, and only one, JWS Provider shall be set'
        );
    }

    /**
     * @test
     * @dataProvider multipleJwsProvider
     */
    public function invalidIfSeveralJwsProviderAreSet(array $conf): void
    {
        $conf['enabled'] = true;
        $conf['subject'] = 'https://foo.bar';
        $this->assertConfigurationIsInvalid(
            [
                [
                    'vapid' => $conf,
                ],
            ],
            'Invalid configuration for path "webpush.vapid": One, and only one, JWS Provider shall be set'
        );
    }

    public function multipleJwsProvider(): array
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
