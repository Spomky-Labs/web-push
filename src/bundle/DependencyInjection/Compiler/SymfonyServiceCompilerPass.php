<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2020-2021 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace WebPush\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use WebPush\Bundle\Service\WebPush;
use WebPush\ExtensionManager;

final class SymfonyServiceCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('http_client')) {
            return;
        }

        $definition = new Definition(WebPush::class, [
            new Reference('http_client'),
            new Reference('webpush.request_factory'),
            new Reference(ExtensionManager::class),
        ]);
        $definition->setPublic(true);
        $container->setDefinition('web_push.service', $definition);
    }
}
