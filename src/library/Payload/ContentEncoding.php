<?php

declare(strict_types=1);

namespace WebPush\Payload;

use WebPush\RequestData;
use WebPush\SubscriptionInterface;

interface ContentEncoding
{
    public function encode(string $payload, RequestData $requestData, SubscriptionInterface $subscription): void;

    public function name(): string;
}
