<?php

declare(strict_types=1);

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
