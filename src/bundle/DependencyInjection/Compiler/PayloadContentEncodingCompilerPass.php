<?php

declare(strict_types=1);

namespace WebPush\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use WebPush\Payload\PayloadExtension;

final class PayloadContentEncodingCompilerPass implements CompilerPassInterface
{
    public const TAG = 'webpush_payload_content_encoding';

    public function process(ContainerBuilder $container): void
    {
        if (! $container->hasDefinition(PayloadExtension::class)) {
            return;
        }

        $definition = $container->getDefinition(PayloadExtension::class);
        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall('addContentEncoding', [new Reference($id)]);
        }
    }
}
