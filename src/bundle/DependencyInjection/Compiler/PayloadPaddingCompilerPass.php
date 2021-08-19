<?php

declare(strict_types=1);

namespace WebPush\Bundle\DependencyInjection\Compiler;

use function is_int;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WebPush\Payload\AES128GCM;
use WebPush\Payload\AESGCM;

final class PayloadPaddingCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $this->processForService($container, AES128GCM::class, 'webpush.payload.aes128gcm.padding');
        $this->processForService($container, AESGCM::class, 'webpush.payload.aesgcm.padding');
    }

    private function processForService(ContainerBuilder $container, string $class, string $parameter): void
    {
        if (!$container->hasDefinition($class)) {
            return;
        }

        $padding = $container->getParameter($parameter);
        $definition = $container->getDefinition($class);
        switch (true) {
            case 'none' === $padding:
                $definition->addMethodCall('noPadding');
                break;
            case 'recommended' === $padding:
                $definition->addMethodCall('recommendedPadding');
                break;
            case 'max' === $padding:
                $definition->addMethodCall('maxPadding');
                break;
            case is_int($padding):
                $definition->addMethodCall('customPadding', [$padding]);
                break;
        }
    }
}
