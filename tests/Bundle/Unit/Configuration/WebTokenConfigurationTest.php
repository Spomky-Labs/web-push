<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\Unit\Configuration;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use WebPush\Base64Url;

/**
 * @internal
 */
final class WebTokenConfigurationTest extends AbstractConfigurationTestCase
{
    #[Test]
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
                            'public_key' => Base64Url::encode(
                                '00000000000000000000000000000000000000000000000000000000000000000'
                            ),
                        ],
                    ],
                ],
            ],
            [
                'logger' => null,
                'http_client' => HttpClientInterface::class,
                'vapid' => [
                    'enabled' => true,
                    'token_lifetime' => 'now +1hour',
                    'subject' => 'https://foo.bar',
                    'web_token' => [
                        'enabled' => true,
                        'private_key' => Base64Url::encode('00000000000000000000000000000000'),
                        'public_key' => Base64Url::encode(
                            '00000000000000000000000000000000000000000000000000000000000000000'
                        ),
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
}
