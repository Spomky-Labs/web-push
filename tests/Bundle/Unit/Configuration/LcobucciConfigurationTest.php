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

namespace WebPush\Tests\Bundle\Unit\Configuration;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use WebPush\Base64Url;

/**
 * @group unit
 *
 * @internal
 */
class LcobucciConfigurationTest extends AbstractConfigurationTest
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
                        'lcobucci' => [
                            'enabled' => true,
                        ],
                    ],
                ],
            ],
            'The child config "private_key" under "webpush.vapid.lcobucci" must be configured: The VAPID private key'
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
                        'lcobucci' => [
                            'enabled' => true,
                            'private_key' => Base64Url::encode('00000000000000000000000000000000'),
                        ],
                    ],
                ],
            ],
            'The child config "public_key" under "webpush.vapid.lcobucci" must be configured: The VAPID public key'
        );
    }

    /**
     * @test
     */
    public function validVapidLcobucciConfiguration(): void
    {
        $this->assertProcessedConfigurationEquals(
            [
                [
                    'vapid' => [
                        'enabled' => true,
                        'subject' => 'https://foo.bar',
                        'lcobucci' => [
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
                'doctrine_mapping' => false,
                'vapid' => [
                    'enabled' => true,
                    'token_lifetime' => 'now +1hour',
                    'subject' => 'https://foo.bar',
                    'web_token' => ['enabled' => false],
                    'lcobucci' => [
                        'enabled' => true,
                        'private_key' => Base64Url::encode('00000000000000000000000000000000'),
                        'public_key' => Base64Url::encode('00000000000000000000000000000000000000000000000000000000000000000'),
                    ],
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
