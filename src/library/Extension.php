<?php

declare(strict_types=1);

namespace WebPush;

interface Extension
{
    public function process(
        RequestData $requestData,
        NotificationInterface $notification,
        SubscriptionInterface $subscription
    ): void;
}
