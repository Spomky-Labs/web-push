<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\Unit\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use WebPush\Bundle\DependencyInjection\Compiler\ExtensionCompilerPass;
use WebPush\ExtensionManager;

/**
 * @internal
 */
final class ExtensionCompilerPassTest extends AbstractCompilerPassTestCase
{
    #[Test]
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
            [new Reference('collected_service')]
        );
    }

    #[Test]
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
        $container->addCompilerPass(new ExtensionCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
    }
}
