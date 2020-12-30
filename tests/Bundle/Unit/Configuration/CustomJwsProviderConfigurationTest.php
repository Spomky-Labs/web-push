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

namespace WebPush\Tests\Bundle\Unit\Configuration;

/**
 * @group unit
 *
 * @internal
 */
class CustomJwsProviderConfigurationTest extends AbstractConfigurationTest
{
    /**
     * @test
     */
    public function invalidIfPrivateKeyIsMissing(): void
    {
        $this->assertConfigurationIsInvalid(
            [
                [
                    'vapid' => [
                        'enabled' => true,
                        'subject' => 'https://foo.bar',
                        'custom' => [
                            'enabled' => true,
                        ],
                    ],
                ],
            ],
            'The child config "id" under "webpush.vapid.custom" must be configured: The custom JWS Provider service ID'
        );
    }
}
