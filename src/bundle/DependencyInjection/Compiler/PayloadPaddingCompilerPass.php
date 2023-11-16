<?php

declare(strict_types=1);

namespace WebPush\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WebPush\Payload\AES128GCM;
use WebPush\Payload\AESGCM;
use function is_int;

final class PayloadPaddingCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $this->processForService($container, AES128GCM::class, 'webpush.payload.aes128gcm.padding');
        $this->processForService($container, AESGCM::class, 'webpush.payload.aesgcm.padding');
    }

    private function processForService(ContainerBuilder $container, string $class, string $parameter): void
    {
        if (! $container->hasDefinition($class)) {
            return;
        }

        $padding = $container->getParameter($parameter);
        $definition = $container->getDefinition($class);
        switch (true) {
            case $padding === 'none':
                $definition->addMethodCall('noPadding');
                break;
            case $padding === 'recommended':
                $definition->addMethodCall('recommendedPadding');
                break;
            case $padding === 'max':
                $definition->addMethodCall('maxPadding');
                break;
            case is_int($padding):
                $definition->addMethodCall('customPadding', [$padding]);
                break;
        }
    }
}
