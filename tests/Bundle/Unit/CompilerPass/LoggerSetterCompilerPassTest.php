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

namespace WebPush\Tests\Bundle\Unit\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use WebPush\Bundle\DependencyInjection\Compiler\LoggerSetterCompilerPass;

/**
 * @group unit
 *
 * @internal
 */
class LoggerSetterCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @test
     */
    public function ifTheLoggerIsSetThenItIsSetToAllTaggedServices(): void
    {
        $collectingService = new Definition();
        $this->setDefinition(LoggerInterface::class, $collectingService);
        $this->container->setAlias(LoggerSetterCompilerPass::SERVICE, LoggerInterface::class);

        $collectedService = new Definition();
        $collectedService->addTag(LoggerSetterCompilerPass::TAG);
        $this->setDefinition('collected_service', $collectedService);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'collected_service',
            'setLogger',
            [
                new Reference(LoggerSetterCompilerPass::SERVICE),
            ]
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new LoggerSetterCompilerPass());
    }
}
