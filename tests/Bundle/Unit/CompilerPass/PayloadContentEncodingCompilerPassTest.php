<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\Unit\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use WebPush\Bundle\DependencyInjection\Compiler\PayloadContentEncodingCompilerPass;
use WebPush\Payload\PayloadExtension;

/**
 * @internal
 */
final class PayloadContentEncodingCompilerPassTest extends AbstractCompilerPassTestCase
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
            [new Reference('collected_service')]
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
