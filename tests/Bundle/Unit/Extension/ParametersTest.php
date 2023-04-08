<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\Unit\Extension;

use PHPUnit\Framework\Attributes\Test;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use WebPush\Base64Url;
use WebPush\VAPID\JWSProvider;

/**
 * @internal
 */
final class ParametersTest extends AbstractExtensionTestCase
{
    #[Test]
    public function theMinimalParametersAndAliasesAreSet(): void
    {
        $this->load();

        $this->assertContainerBuilderHasAlias('webpush.logger', LoggerInterface::class);
        $this->assertContainerBuilderHasAlias('webpush.http_client', HttpClientInterface::class);

        $this->assertContainerBuilderHasParameter('webpush.payload.aesgcm.cache_lifetime', 'now + 30min');
        $this->assertContainerBuilderHasParameter('webpush.payload.aesgcm.padding', 'recommended');
        $this->assertContainerBuilderHasAlias('webpush.payload.aesgcm.cache', CacheItemPoolInterface::class);

        $this->assertContainerBuilderHasParameter('webpush.payload.aes128gcm.cache_lifetime', 'now + 30min');
        $this->assertContainerBuilderHasParameter('webpush.payload.aes128gcm.padding', 'recommended');
        $this->assertContainerBuilderHasAlias('webpush.payload.aes128gcm.cache', CacheItemPoolInterface::class);
    }

    #[Test]
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
                    'public_key' => Base64Url::encode(
                        '00000000000000000000000000000000000000000000000000000000000000000'
                    ),
                ],
            ],
        ]);

        $this->assertContainerBuilderHasParameter('webpush.vapid.subject', 'foo@bar.com');
        $this->assertContainerBuilderHasParameter('webpush.vapid.token_lifetime', 'now +1 hour');
        $this->assertContainerBuilderHasParameter(
            'webpush.vapid.web_token.private_key',
            Base64Url::encode('00000000000000000000000000000000')
        );
        $this->assertContainerBuilderHasParameter(
            'webpush.vapid.web_token.public_key',
            Base64Url::encode('00000000000000000000000000000000000000000000000000000000000000000')
        );
    }

    #[Test]
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
                    'public_key' => Base64Url::encode(
                        '00000000000000000000000000000000000000000000000000000000000000000'
                    ),
                ],
            ],
        ]);

        $this->assertContainerBuilderHasParameter('webpush.vapid.subject', 'foo@bar.com');
        $this->assertContainerBuilderHasParameter('webpush.vapid.token_lifetime', 'now +1 hour');
        $this->assertContainerBuilderHasParameter(
            'webpush.vapid.lcobucci.private_key',
            Base64Url::encode('00000000000000000000000000000000')
        );
        $this->assertContainerBuilderHasParameter(
            'webpush.vapid.lcobucci.public_key',
            Base64Url::encode('00000000000000000000000000000000000000000000000000000000000000000')
        );
    }

    #[Test]
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
            'http_client' => HttpClientInterface::class,

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
