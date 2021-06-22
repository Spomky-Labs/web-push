<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2020-2021 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace WebPush\Tests\Bundle\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WebPush\Extension;
use WebPush\Payload\PayloadExtension;
use WebPush\PreferAsyncExtension;
use WebPush\TopicExtension;
use WebPush\TTLExtension;
use WebPush\UrgencyExtension;
use WebPush\VAPID\VAPIDExtension;

/**
 * @group functional
 *
 * @internal
 */
class ExtensionTest extends KernelTestCase
{
    /**
     * @test
     * @dataProvider  listOfPayloadExtensions
     *
     * @param mixed $class
     */
    public function taggedExtensionsAreAutoConfigured($class): void
    {
        self::bootKernel();
        $service = self::getContainer()->get($class);
        static::assertInstanceOf(Extension::class, $service);
    }

    public function listOfPayloadExtensions(): array
    {
        return [
            [
                PreferAsyncExtension::class,
            ],
            [
                TopicExtension::class,
            ],
            [
                TTLExtension::class,
            ],
            [
                UrgencyExtension::class,
            ],
            [
                PayloadExtension::class,
            ],
            [
                VAPIDExtension::class,
            ],
        ];
    }
}
