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

use WebPush\Payload\AESGCM;

/**
 * @group functional
 *
 * @internal
 */
class AESGCMConfigurationTest extends AbstractConfigurationTest
{
    /**
     * @test
     *
     * @param string|int|bool $padding
     * @dataProvider invalidPaddings
     */
    public function invalidIfThePaddingDoesNotFulfillTheConstraints($padding): void
    {
        $this->assertConfigurationIsInvalid(
            [
                [
                    'payload' => [
                        'aesgcm' => [
                            'padding' => $padding,
                        ],
                    ],
                ],
            ],
            'Invalid configuration for path "webpush.payload.aesgcm.padding": The padding must have one of the following value: none, recommended, max or an integer between 0 and 4078'
        );
    }

    public function invalidPaddings(): array
    {
        return [
            ['min'],
            [-1],
            [AESGCM::PADDING_MAX + 1],
        ];
    }
}
