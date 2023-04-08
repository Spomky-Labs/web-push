<?php

declare(strict_types=1);

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
        if (! $container->hasDefinition('http_client')) {
            return;
        }

        $definition = new Definition(WebPush::class, [
            new Reference('http_client'),
            new Reference(ExtensionManager::class),
        ]);
        $definition->setPublic(true);
        $container->setDefinition('web_push.service', $definition);
    }
}
