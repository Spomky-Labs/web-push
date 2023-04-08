<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WebPush\Bundle\DependencyInjection\Compiler\ExtensionCompilerPass;
use WebPush\Bundle\DependencyInjection\Compiler\LoggerSetterCompilerPass;
use WebPush\Bundle\DependencyInjection\Compiler\PayloadCacheCompilerPass;
use WebPush\Bundle\DependencyInjection\Compiler\PayloadContentEncodingCompilerPass;
use WebPush\Bundle\DependencyInjection\Compiler\PayloadPaddingCompilerPass;
use WebPush\Bundle\DependencyInjection\WebPushExtension;
use WebPush\Bundle\WebPushBundle;

/**
 * @internal
 */
final class BundleTest extends TestCase
{
    #[Test]
    public function theBundleHasTheCorrectExtension(): void
    {
        $bundle = new WebPushBundle();

        static::assertInstanceOf(WebPushExtension::class, $bundle->getContainerExtension());
    }

    #[Test]
    #[DataProvider('compilerPasses')]
    public function theBundleHasTheCompilerPass(string $class): void
    {
        $containerBuilder = new ContainerBuilder();
        $bundle = new WebPushBundle();
        $bundle->build($containerBuilder);

        $passes = $containerBuilder->getCompiler()
            ->getPassConfig()
            ->getPasses()
        ;
        $found = false;
        foreach ($passes as $pass) {
            if ($pass instanceof $class) {
                $found = true;
                break;
            }
        }

        static::assertTrue($found, 'Unable to find the compiler pass ' . $class);
    }

    public static function compilerPasses(): array
    {
        return [
            [ExtensionCompilerPass::class],
            [LoggerSetterCompilerPass::class],
            [PayloadCacheCompilerPass::class],
            [PayloadContentEncodingCompilerPass::class],
            [PayloadPaddingCompilerPass::class],
        ];
    }
}
