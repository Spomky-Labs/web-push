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

namespace WebPush\Tests\Bundle\Unit;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use WebPush\Base64Url;

/**
 * @group functional
 *
 * @internal
 */
class WebTokenConfigurationTest extends AbstractConfigurationTest
{
    /**
     * @test
     */
    public function invalidIfPrivateKeyIsMissing(): void
    {
        $this->assertConfigurationIsInvalid(
            [
                [
                    'vapid' => [
                        'enabled' => true,
                        'subject' => 'https://foo.bar',
                        'web-token' => [
                            'enabled' => true,
                        ],
                    ],
                ],
            ],
            'The child config "private_key" under "webpush.vapid.web_token" must be configured: The VAPID private key'
        );
    }

    /**
     * @test
     */
    public function invalidIfPrivateKeyLengthIsIncorrect(): void
    {
        $this->assertConfigurationIsInvalid(
            [
                [
                    'vapid' => [
                        'enabled' => true,
                        'subject' => 'https://foo.bar',
                        'web-token' => [
                            'enabled' => true,
                            'private_key' => 'ABCDEF',
                            'public_key' => 'abcde',
                        ],
                    ],
                ],
            ],
            'Invalid configuration for path "webpush.vapid.web_token.private_key": Invalid private key length'
        );
    }

    /**
     * @test
     */
    public function invalidIfPublicKeyIsMissing(): void
    {
        $this->assertConfigurationIsInvalid(
            [
                [
                    'vapid' => [
                        'enabled' => true,
                        'subject' => 'https://foo.bar',
                        'web-token' => [
                            'enabled' => true,
                            'private_key' => Base64Url::encode('00000000000000000000000000000000'),
                        ],
                    ],
                ],
            ],
            'The child config "public_key" under "webpush.vapid.web_token" must be configured: The VAPID public key'
        );
    }

    /**
     * @test
     */
    public function invalidIfPublicKeyLengthIsIncorrect(): void
    {
        $this->assertConfigurationIsInvalid(
            [
                [
                    'vapid' => [
                        'enabled' => true,
                        'subject' => 'https://foo.bar',
                        'web-token' => [
                            'enabled' => true,
                            'private_key' => Base64Url::encode('00000000000000000000000000000000'),
                            'public_key' => Base64Url::encode('00000000000000000000000000000000'),
                        ],
                    ],
                ],
            ],
            'Invalid configuration for path "webpush.vapid.web_token.public_key": Invalid public key length'
        );
    }

    /**
     * @test
     */
    public function validVapidWebTokenConfiguration(): void
    {
        $this->assertProcessedConfigurationEquals(
            [
                [
                    'vapid' => [
                        'enabled' => true,
                        'subject' => 'https://foo.bar',
                        'web-token' => [
                            'enabled' => true,
                            'private_key' => Base64Url::encode('00000000000000000000000000000000'),
                            'public_key' => Base64Url::encode('00000000000000000000000000000000000000000000000000000000000000000'),
                        ],
                    ],
                ],
            ],
            [
                'logger' => null,
                'http_client' => ClientInterface::class,
                'request_factory' => RequestFactoryInterface::class,
                'vapid' => [
                    'enabled' => true,
                    'cache' => null,
                    'cache_lifetime' => 'now + 30min',
                    'token_lifetime' => 'now +1hour',
                    'subject' => 'https://foo.bar',
                    'web_token' => [
                        'enabled' => true,
                        'private_key' => Base64Url::encode('00000000000000000000000000000000'),
                        'public_key' => Base64Url::encode('00000000000000000000000000000000000000000000000000000000000000000'),
                    ],
                    'lcobucci' => ['enabled' => false],
                    'custom' => ['enabled' => false],
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
}
