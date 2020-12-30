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

namespace WebPush\Tests\Bundle\Unit\Extension;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;
use WebPush\Base64Url;
use WebPush\VAPID\JWSProvider;

/**
 * @group unit
 *
 * @internal
 */
class ParametersTest extends AbstractExtensionTest
{
    /**
     * @test
     */
    public function theMinimalParametersAndAliasesAreSet(): void
    {
        $this->load();

        $this->assertContainerBuilderHasAlias('webpush.logger', LoggerInterface::class);
        $this->assertContainerBuilderHasAlias('webpush.http_client', ClientInterface::class);
        $this->assertContainerBuilderHasAlias('webpush.request_factory', RequestFactoryInterface::class);

        $this->assertContainerBuilderHasParameter('webpush.payload.aesgcm.cache_lifetime', 'now + 30min');
        $this->assertContainerBuilderHasParameter('webpush.payload.aesgcm.padding', 'recommended');
        $this->assertContainerBuilderHasAlias('webpush.payload.aesgcm.cache', CacheItemPoolInterface::class);

        $this->assertContainerBuilderHasParameter('webpush.payload.aes128gcm.cache_lifetime', 'now + 30min');
        $this->assertContainerBuilderHasParameter('webpush.payload.aes128gcm.padding', 'recommended');
        $this->assertContainerBuilderHasAlias('webpush.payload.aes128gcm.cache', CacheItemPoolInterface::class);
    }

    /**
     * @test
     */
    public function theVapidWebTokenParametersAndAliasesAreSet(): void
    {
        $this->load([
            'vapid' => [
                'enabled' => true,
                'subject' => 'foo@bar.com',
                'token_lifetime' => 'now +1 hour',
                'web-token' => [
                    'enabled' => true,
                    'private_key' => Base64Url::encode('00000000000000000000000000000000'),
                    'public_key' => Base64Url::encode('00000000000000000000000000000000000000000000000000000000000000000'),
                ],
            ],
        ]);

        $this->assertContainerBuilderHasParameter('webpush.vapid.subject', 'foo@bar.com');
        $this->assertContainerBuilderHasParameter('webpush.vapid.token_lifetime', 'now +1 hour');
        $this->assertContainerBuilderHasParameter('webpush.vapid.web_token.private_key', Base64Url::encode('00000000000000000000000000000000'));
        $this->assertContainerBuilderHasParameter('webpush.vapid.web_token.public_key', Base64Url::encode('00000000000000000000000000000000000000000000000000000000000000000'));
    }

    /**
     * @test
     */
    public function theVapidLcobucciParametersAndAliasesAreSet(): void
    {
        $this->load([
            'vapid' => [
                'enabled' => true,
                'subject' => 'foo@bar.com',
                'token_lifetime' => 'now +1 hour',
                'lcobucci' => [
                    'enabled' => true,
                    'private_key' => Base64Url::encode('00000000000000000000000000000000'),
                    'public_key' => Base64Url::encode('00000000000000000000000000000000000000000000000000000000000000000'),
                ],
            ],
        ]);

        $this->assertContainerBuilderHasParameter('webpush.vapid.subject', 'foo@bar.com');
        $this->assertContainerBuilderHasParameter('webpush.vapid.token_lifetime', 'now +1 hour');
        $this->assertContainerBuilderHasParameter('webpush.vapid.lcobucci.private_key', Base64Url::encode('00000000000000000000000000000000'));
        $this->assertContainerBuilderHasParameter('webpush.vapid.lcobucci.public_key', Base64Url::encode('00000000000000000000000000000000000000000000000000000000000000000'));
    }

    /**
     * @test
     */
    public function theVapidCustomParametersAndAliasesAreSet(): void
    {
        $this->load([
            'vapid' => [
                'enabled' => true,
                'subject' => 'foo@bar.com',
                'token_lifetime' => 'now +1 hour',
                'custom' => [
                    'enabled' => true,
                    'id' => 'custom_service_id',
                ],
            ],
        ]);

        $this->assertContainerBuilderHasParameter('webpush.vapid.subject', 'foo@bar.com');
        $this->assertContainerBuilderHasParameter('webpush.vapid.token_lifetime', 'now +1 hour');
        $this->assertContainerBuilderHasAlias(JWSProvider::class, 'custom_service_id');
    }

    protected function getMinimalConfiguration(): array
    {
        return [
            'logger' => LoggerInterface::class,
            'http_client' => ClientInterface::class,
            'request_factory' => RequestFactoryInterface::class,
            'payload' => [
                'aes128gcm' => [
                    'padding' => 'recommended',
                    'cache' => CacheItemPoolInterface::class,
                    'cache_lifetime' => 'now + 30min',
                ],
                'aesgcm' => [
                    'padding' => 'recommended',
                    'cache' => CacheItemPoolInterface::class,
                    'cache_lifetime' => 'now + 30min',
                ],
            ],
        ];
    }
}
