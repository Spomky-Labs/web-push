<?php

declare(strict_types=1);

namespace WebPush\Bundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
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

final class WebPushBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new WebPushExtension('webpush');
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new ExtensionCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
        $container->addCompilerPass(new LoggerSetterCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
        $container->addCompilerPass(new PayloadContentEncodingCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
        $container->addCompilerPass(new PayloadCacheCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
        $container->addCompilerPass(new PayloadPaddingCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
        $container->addCompilerPass(new SymfonyServiceCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
    }
}
