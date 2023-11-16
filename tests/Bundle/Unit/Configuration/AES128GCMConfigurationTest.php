<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\Unit\Configuration;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use WebPush\Payload\AES128GCM;

/**
 * @internal
 */
final class AES128GCMConfigurationTest extends AbstractConfigurationTestCase
{
    #[Test]
    #[DataProvider('validPaddings')]
    public function validPadding(string|int|bool $padding): void
    {
        $this->assertProcessedConfigurationEquals(
            [
                [
                    'payload' => [
                        'aes128gcm' => [
                            'padding' => $padding,
                        ],
                    ],
                ],
            ],
            [
                'logger' => null,
                'http_client' => HttpClientInterface::class,

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
                        'padding' => $padding,
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

    public static function validPaddings(): iterable
    {
        yield ['none'];
        yield ['recommended'];
        yield ['max'];
        yield [0];
        yield [AES128GCM::PADDING_MAX];
    }
}
