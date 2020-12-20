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

final class EventDispatcherSetterCompilerPass implements CompilerPassInterface
{
    public const TAG = 'webpush_dispatchable';
    public const SERVICE = 'webpush.event_dispatcher';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasAlias(self::SERVICE)) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        foreach ($taggedServices as $id => $attributes) {
            $definition = $container->getDefinition($id);
            $definition->addMethodCall('setEventDispatcher', [new Reference(self::SERVICE)]);
        }
    }
}
