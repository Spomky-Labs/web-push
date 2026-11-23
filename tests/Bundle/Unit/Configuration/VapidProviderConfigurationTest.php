<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\Unit\Configuration;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

/**
 * @internal
 */
final class VapidProviderConfigurationTest extends AbstractConfigurationTestCase
{
    /**
     * @param array<string, array<string, mixed>> $providers
     */
    #[Test]
    #[DataProvider('notExactlyOneProvider')]
    public function vapidRequiresExactlyOneProvider(array $providers): void
    {
        // Given
        $configuration = [
            'vapid' => [
                'enabled' => true,
                'subject' => 'https://foo.bar',
                ...$providers,
            ],
        ];

        // Then
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('One, and only one, JWS Provider shall be set');

        // When
        (new Processor())->processConfiguration($this->getConfiguration(), [$configuration]);
    }

    #[Test]
    public function vapidAcceptsTheCustomProviderAlone(): void
    {
        // Given
        $configuration = [
            'vapid' => [
                'enabled' => true,
                'subject' => 'https://foo.bar',
                'custom' => self::customProvider(),
            ],
        ];

        // When
        $processed = (new Processor())->processConfiguration($this->getConfiguration(), [$configuration]);

        // Then
        static::assertSame([
            'enabled' => true,
            'id' => 'app.jws_provider',
        ], $processed['vapid']['custom']);
        static::assertSame([
            'enabled' => false,
        ], $processed['vapid']['web_token']);
        static::assertSame([
            'enabled' => false,
        ], $processed['vapid']['lcobucci']);
    }

    /**
     * @return iterable<string, array{array<string, array<string, mixed>>}>
     */
    public static function notExactlyOneProvider(): iterable
    {
        yield 'no provider' => [[]];
        yield 'web_token and lcobucci' => [[
            'web_token' => self::keyPairProvider(),
            'lcobucci' => self::keyPairProvider(),
        ]];
        yield 'web_token and custom' => [[
            'web_token' => self::keyPairProvider(),
            'custom' => self::customProvider(),
        ]];
        yield 'all three providers' => [[
            'web_token' => self::keyPairProvider(),
            'lcobucci' => self::keyPairProvider(),
            'custom' => self::customProvider(),
        ]];
    }

    /**
     * @return array<string, mixed>
     */
    private static function keyPairProvider(): array
    {
        return [
            'enabled' => true,
            'private_key' => 'private',
            'public_key' => 'public',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function customProvider(): array
    {
        return [
            'enabled' => true,
            'id' => 'app.jws_provider',
        ];
    }
}
