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

namespace WebPush\Tests\Bundle\Unit\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use WebPush\Bundle\DependencyInjection\Compiler\PayloadCacheCompilerPass;
use WebPush\Payload\AES128GCM;
use WebPush\Payload\AESGCM;

/**
 * @group unit
 *
 * @internal
 */
class PayloadCacheCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @test
     * @dataProvider cacheParameters
     *
     * @param mixed $lifetimeParameterName
     */
    public function ifTheCacheIsSetAndTheDefinitionExistThenItTheCacheIsSet(string $class, string $cacheDefinition, $lifetimeParameterName, string $lifetimeParameterValue): void
    {
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
            [
                new Reference($cacheDefinition),
                $lifetimeParameterValue,
            ]
        );
    }

    public function cacheParameters(): array
    {
        return [
            [
                AES128GCM::class,
                'webpush.payload.aes128gcm.cache',
                'webpush.payload.aes128gcm.cache_lifetime',
                'now +1 day',
            ],
            [
                AESGCM::class,
                'webpush.payload.aesgcm.cache',
                'webpush.payload.aesgcm.cache_lifetime',
                'now +1 day',
            ],
        ];
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new PayloadCacheCompilerPass());
    }
}
