<?php

declare(strict_types=1);

namespace WebPush;

interface WebPushService
{
    public function send(
        NotificationInterface $notification,
        SubscriptionInterface $subscription
    ): StatusReportInterface;
}
