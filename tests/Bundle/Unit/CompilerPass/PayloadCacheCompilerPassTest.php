<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\Unit\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use WebPush\Bundle\DependencyInjection\Compiler\PayloadCacheCompilerPass;
use WebPush\Payload\AES128GCM;
use WebPush\Payload\AESGCM;

/**
 * @internal
 */
final class PayloadCacheCompilerPassTest extends AbstractCompilerPassTestCase
{
    #[Test]
    #[DataProvider('cacheParameters')]
    public function ifTheCacheIsSetAndTheDefinitionExistThenItTheCacheIsSet(
        string $class,
        string $cacheDefinition,
        $lifetimeParameterName,
        string $lifetimeParameterValue
    ): void {
        $collectingService = new Definition();
        $this->setDefinition($class, $collectingService);

        $collectedService = new Definition();
        $this->setDefinition(CacheItemPoolInterface::class, $collectedService);
        $this->container->setAlias($cacheDefinition, CacheItemPoolInterface::class);

        $this->setParameter($lifetimeParameterName, $lifetimeParameterValue);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            $class,
            'setCache',
            [new Reference($cacheDefinition), $lifetimeParameterValue]
        );
    }

    public static function cacheParameters(): iterable
    {
        yield [
            AES128GCM::class,
            'webpush.payload.aes128gcm.cache',
            'webpush.payload.aes128gcm.cache_lifetime',
            'now +1 day',
        ];
        yield [AESGCM::class, 'webpush.payload.aesgcm.cache', 'webpush.payload.aesgcm.cache_lifetime', 'now +1 day'];
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new PayloadCacheCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
    }
}
