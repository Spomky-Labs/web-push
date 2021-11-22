<?php

declare(strict_types=1);

namespace WebPush\Bundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use JetBrains\PhpStorm\Pure;
use function realpath;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use WebPush\Bundle\DependencyInjection\Compiler\ExtensionCompilerPass;
use WebPush\Bundle\DependencyInjection\Compiler\LoggerSetterCompilerPass;
use WebPush\Bundle\DependencyInjection\Compiler\PayloadCacheCompilerPass;
use WebPush\Bundle\DependencyInjection\Compiler\PayloadContentEncodingCompilerPass;
use WebPush\Bundle\DependencyInjection\Compiler\PayloadPaddingCompilerPass;
use WebPush\Bundle\DependencyInjection\Compiler\SymfonyServiceCompilerPass;
use WebPush\Bundle\DependencyInjection\WebPushExtension;
use WebPush\Bundle\Exception\InitializationException;

final class WebPushBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    #[Pure]
    public function getContainerExtension(): ExtensionInterface
    {
        return new WebPushExtension('webpush');
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new ExtensionCompilerPass());
        $container->addCompilerPass(new LoggerSetterCompilerPass());
        $container->addCompilerPass(new PayloadContentEncodingCompilerPass());
        $container->addCompilerPass(new PayloadCacheCompilerPass());
        $container->addCompilerPass(new PayloadPaddingCompilerPass());
        $container->addCompilerPass(new SymfonyServiceCompilerPass());

        $this->registerMappings($container);
    }

    private function registerMappings(ContainerBuilder $container): void
    {
        if (! class_exists(DoctrineOrmMappingsPass::class)) {
            return;
        }

        $realPath = realpath(__DIR__ . '/Resources/config/doctrine-mapping');
        if ($realPath === false) {
            throw new InitializationException('Unaqble to get the real path for the doctrine mapping');
        }
        $mappings = [
            $realPath => 'WebPush',
        ];
        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createXmlMappingDriver($mappings, [], 'webpush.doctrine_mapping')
        );
    }
}
