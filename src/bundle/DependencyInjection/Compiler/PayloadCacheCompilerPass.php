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

namespace WebPush\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use WebPush\Payload\AES128GCM;
use WebPush\Payload\AESGCM;

final class PayloadCacheCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $this->processForService($container, AES128GCM::class, 'webpush.payload.aes128gcm.cache', 'webpush.payload.aes128gcm.cache_lifetime');
        $this->processForService($container, AESGCM::class, 'webpush.payload.aesgcm.cache', 'webpush.payload.aesgcm.cache_lifetime');
    }

    private function processForService(ContainerBuilder $container, string $class, string $cache, string $parameter): void
    {
        if (!$container->hasDefinition($class) || !$container->hasAlias($cache)) {
            return;
        }

        $cacheLifetime = $container->getParameter($parameter);
        $definition = $container->getDefinition($class);
        $definition->addMethodCall('setCache', [
            new Reference($cache),
            $cacheLifetime,
        ]);
    }
}
