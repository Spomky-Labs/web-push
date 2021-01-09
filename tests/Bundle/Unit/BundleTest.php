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

namespace WebPush\Tests\Bundle\Unit;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClassExistsMock;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WebPush\Bundle\DependencyInjection\Compiler;
use WebPush\Bundle\DependencyInjection\WebPushExtension;
use WebPush\Bundle\WebPushBundle;

/**
 * @group unit
 *
 * @internal
 */
class BundleTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        ClassExistsMock::register(WebPushBundle::class);
    }

    protected function setUp(): void
    {
        ClassExistsMock::withMockedClasses([]);
    }

    public function tearDown(): void
    {
        ClassExistsMock::withMockedClasses([]);
    }

    /**
     * @test
     */
    public function theBundleHasTheCorrectExtension(): void
    {
        $bundle = new WebPushBundle();

        static::assertInstanceOf(WebPushExtension::class, $bundle->getContainerExtension());
    }

    /**
     * @test
     * @dataProvider compilerPasses
     */
    public function theBundleHasTheCompilerPass(string $class): void
    {
        $containerBuilder = new ContainerBuilder();
        $bundle = new WebPushBundle();
        $bundle->build($containerBuilder);

        $passes = $containerBuilder->getCompiler()->getPassConfig()->getPasses();
        $found = false;
        foreach ($passes as $pass) {
            if ($pass instanceof $class) {
                $found = true;
                break;
            }
        }

        static::assertTrue($found, 'Unable to find the compiler pass '.$class);
    }

    /**
     * @test
     */
    public function theBundleDoesNotAddDoctrineCompilerPassesIfNotAvailableHasTheCompilerPass(): void
    {
        ClassExistsMock::withMockedClasses([DoctrineOrmMappingsPass::class => false]);

        $containerBuilder = new ContainerBuilder();
        $bundle = new WebPushBundle();
        $bundle->build($containerBuilder);

        $passes = $containerBuilder->getCompiler()->getPassConfig()->getPasses();
        $found = false;
        foreach ($passes as $pass) {
            if ($pass instanceof DoctrineOrmMappingsPass) {
                $found = true;
                break;
            }
        }

        static::assertFalse($found, 'The compiler pass DoctrineOrmMappingsPass has been found');
    }

    public function compilerPasses(): array
    {
        return [
            [Compiler\ExtensionCompilerPass::class],
            [Compiler\LoggerSetterCompilerPass::class],
            [Compiler\PayloadCacheCompilerPass::class],
            [Compiler\PayloadContentEncodingCompilerPass::class],
            [Compiler\PayloadPaddingCompilerPass::class],
            [DoctrineOrmMappingsPass::class],
        ];
    }
}
