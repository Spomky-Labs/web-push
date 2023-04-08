<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\Unit\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use WebPush\Bundle\DependencyInjection\Compiler\PayloadPaddingCompilerPass;
use WebPush\Payload\AES128GCM;
use WebPush\Payload\AESGCM;

/**
 * @internal
 */
final class PayloadPaddingCompilerPassTest extends AbstractCompilerPassTestCase
{
    #[Test]
    #[DataProvider('paddingConfigurations')]
    public function thePaddingIsCorrectlySet(
        string $class,
        string $parameterName,
        $configValue,
        string $methodName,
        array $methodParameters
    ): void {
        $collectingService = new Definition();
        $this->setDefinition($class, $collectingService);
        $this->setParameter($parameterName, $configValue);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall($class, $methodName, $methodParameters);
    }

    public static function paddingConfigurations(): array
    {
        return [
            [AES128GCM::class, 'webpush.payload.aes128gcm.padding', 'recommended', 'recommendedPadding', []],
            [AES128GCM::class, 'webpush.payload.aes128gcm.padding', 'none', 'noPadding', []],
            [AES128GCM::class, 'webpush.payload.aes128gcm.padding', 'max', 'maxPadding', []],
            [AES128GCM::class, 'webpush.payload.aes128gcm.padding', 50, 'customPadding', [50]],
            [AESGCM::class, 'webpush.payload.aesgcm.padding', 'recommended', 'recommendedPadding', []],
            [AESGCM::class, 'webpush.payload.aesgcm.padding', 'none', 'noPadding', []],
            [AESGCM::class, 'webpush.payload.aesgcm.padding', 'max', 'maxPadding', []],
            [AESGCM::class, 'webpush.payload.aesgcm.padding', 50, 'customPadding', [50]],
        ];
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new PayloadPaddingCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
    }
}
