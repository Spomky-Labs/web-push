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

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use WebPush\Bundle\DependencyInjection\Configuration;

/**
 * @group functional
 *
 * @internal
 */
class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

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
                    'cache' => null,
                    'cache_lifetime' => 'now + 30min',
                    'token_lifetime' => 'now +1hour',
                    'web_token' => ['enabled' => false],
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
    public function invalidIfSubjectIsSetWhenVapidIsEnabled(): void
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
            'Invalid configuration for path "webpush.vapid": A JWS Provider shall be set'
        );
    }

    protected function getConfiguration(): Configuration
    {
        return new Configuration('webpush');
    }
}
