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
use WebPush\Bundle\DependencyInjection\Compiler\PayloadPaddingCompilerPass;
use WebPush\Payload\AES128GCM;
use WebPush\Payload\AESGCM;

/**
 * @group unit
 *
 * @internal
 */
class PayloadPaddingCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @test
     * @dataProvider paddingConfigurations
     *
     * @param mixed $configValue
     */
    public function thePaddingIsCorrectlySet(string $class, string $parameterName, $configValue, string $methodName, array $methodParameters): void
    {
        $collectingService = new Definition();
        $this->setDefinition($class, $collectingService);
        $this->setParameter($parameterName, $configValue);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            $class,
            $methodName,
            $methodParameters
        );
    }

    public function paddingConfigurations(): array
    {
        return [
            [
                AES128GCM::class,
                'webpush.payload.aes128gcm.padding',
                'recommended',
                'recommendedPadding',
                [],
            ],
            [
                AES128GCM::class,
                'webpush.payload.aes128gcm.padding',
                'none',
                'noPadding',
                [],
            ],
            [
                AES128GCM::class,
                'webpush.payload.aes128gcm.padding',
                'max',
                'maxPadding',
                [],
            ],
            [
                AES128GCM::class,
                'webpush.payload.aes128gcm.padding',
                50,
                'customPadding',
                [50],
            ],
            [
                AESGCM::class,
                'webpush.payload.aesgcm.padding',
                'recommended',
                'recommendedPadding',
                [],
            ],
            [
                AESGCM::class,
                'webpush.payload.aesgcm.padding',
                'none',
                'noPadding',
                [],
            ],
            [
                AESGCM::class,
                'webpush.payload.aesgcm.padding',
                'max',
                'maxPadding',
                [],
            ],
            [
                AESGCM::class,
                'webpush.payload.aesgcm.padding',
                50,
                'customPadding',
                [50],
            ],
        ];
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new PayloadPaddingCompilerPass());
    }
}
