<?php

declare(strict_types=1);

namespace WebPush;

use Psr\Http\Message\RequestInterface;

interface Extension
{
    public function process(
        RequestInterface $request,
        NotificationInterface $notification,
        SubscriptionInterface $subscription
    ): RequestInterface;
}
