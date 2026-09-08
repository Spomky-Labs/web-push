<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\Unit\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use WebPush\Bundle\DependencyInjection\Compiler\SymfonyServiceCompilerPass;
use WebPush\WebPush;

/**
 * @internal
 */
final class SymfonyServiceCompilerPassTest extends AbstractCompilerPassTestCase
{
    #[Test]
    public function theServiceUsesTheLibraryImplementation(): void
    {
        $this->setDefinition('http_client', new Definition());

        $this->compile();

        $this->assertContainerBuilderHasService('web_push.service', WebPush::class);
    }

    #[Test]
    public function theServiceIsNotCreatedWhenNoHttpClientIsAvailable(): void
    {
        $this->compile();

        static::assertFalse($this->container->hasDefinition('web_push.service'));
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new SymfonyServiceCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
    }
}
