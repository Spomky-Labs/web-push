<?php

declare(strict_types=1);

namespace WebPush\Tests;

use WebPush\WebPushService;

/**
 * A service that type-hints the interface instead of the concrete implementation.
 *
 * @internal
 */
final readonly class WebPushServiceConsumer
{
    public function __construct(
        private WebPushService $webPushService
    ) {
    }

    public function getWebPushService(): WebPushService
    {
        return $this->webPushService;
    }
}
