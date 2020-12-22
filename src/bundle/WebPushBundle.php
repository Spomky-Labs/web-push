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

namespace WebPush\Bundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use WebPush\Bundle\DependencyInjection\Compiler\ExtensionCompilerPass;
use WebPush\Bundle\DependencyInjection\Compiler\LoggerSetterCompilerPass;
use WebPush\Bundle\DependencyInjection\Compiler\PayloadCacheCompilerPass;
use WebPush\Bundle\DependencyInjection\Compiler\PayloadContentEncodingCompilerPass;
use WebPush\Bundle\DependencyInjection\Compiler\PayloadPaddingCompilerPass;
use WebPush\Bundle\DependencyInjection\Compiler\VapidCacheCompilerPass;
use WebPush\Bundle\DependencyInjection\WebPushExtension;

final class WebPushBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
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
        $container->addCompilerPass(new VapidCacheCompilerPass());
        $container->addCompilerPass(new PayloadCacheCompilerPass());
        $container->addCompilerPass(new PayloadPaddingCompilerPass());
    }
}
