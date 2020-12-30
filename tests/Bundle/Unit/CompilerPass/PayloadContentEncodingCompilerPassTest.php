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
use WebPush\Bundle\DependencyInjection\Compiler\PayloadContentEncodingCompilerPass;
use WebPush\Payload\PayloadExtension;

/**
 * @group unit
 *
 * @internal
 */
class PayloadContentEncodingCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @test
     */
    public function ifThePayloadExtensionExistsThenTaggedServicesAreAdded(): void
    {
        $collectingService = new Definition();
        $this->setDefinition(PayloadExtension::class, $collectingService);

        $collectedService = new Definition();
        $collectedService->addTag(PayloadContentEncodingCompilerPass::TAG);
        $this->setDefinition('collected_service', $collectedService);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            PayloadExtension::class,
            'addContentEncoding',
            [
                new Reference('collected_service'),
            ]
        );
    }

    /**
     * @test
     */
    public function ifThePayloadExtensionDoesNotExistThenTaggedServicesAreAdded(): void
    {
        $collectedService = new Definition();
        $collectedService->addTag(PayloadContentEncodingCompilerPass::TAG);
        $this->setDefinition('collected_service', $collectedService);

        $this->compile();

        $this->assertContainerBuilderNotHasService(PayloadExtension::class);
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new PayloadContentEncodingCompilerPass());
    }
}
