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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use WebPush\Bundle\DependencyInjection\Compiler\ExtensionCompilerPass;
use WebPush\ExtensionManager;

/**
 * @group unit
 *
 * @internal
 */
class ExtensionCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @test
     */
    public function ifTheExtensionManagerExistsThenTaggedServicesAreAdded(): void
    {
        $collectingService = new Definition();
        $this->setDefinition(ExtensionManager::class, $collectingService);

        $collectedService = new Definition();
        $collectedService->addTag(ExtensionCompilerPass::TAG);
        $this->setDefinition('collected_service', $collectedService);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            ExtensionManager::class,
            'add',
            [
                new Reference('collected_service'),
            ]
        );
    }

    /**
     * @test
     */
    public function ifTheExtensionManagerDoesNotExistThenTaggedServicesAreAdded(): void
    {
        $collectedService = new Definition();
        $collectedService->addTag(ExtensionCompilerPass::TAG);
        $this->setDefinition('collected_service', $collectedService);

        $this->compile();

        $this->assertContainerBuilderNotHasService(ExtensionManager::class);
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ExtensionCompilerPass());
    }
}
