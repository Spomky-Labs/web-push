<?php

declare(strict_types=1);

namespace WebPush\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class LoggerSetterCompilerPass implements CompilerPassInterface
{
    public const TAG = 'webpush_loggable';

    public const SERVICE = 'webpush.logger';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (! $container->hasAlias(self::SERVICE)) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        foreach ($taggedServices as $id => $attributes) {
            $definition = $container->getDefinition($id);
            $definition->addMethodCall('setLogger', [new Reference(self::SERVICE)]);
        }
    }
}
