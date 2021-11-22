<?php

declare(strict_types=1);

namespace WebPush\Payload;

use Psr\Http\Message\RequestInterface;
use WebPush\SubscriptionInterface;

interface ContentEncoding
{
    public function encode(
        string $payload,
        RequestInterface $request,
        SubscriptionInterface $subscription
    ): RequestInterface;

    public function name(): string;
}
