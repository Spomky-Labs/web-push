<?php

declare(strict_types=1);

namespace WebPush;

/**
 * @method list<StatusReportInterface> sendToMultiple(NotificationInterface $notification, array<SubscriptionInterface> $subscriptions)
 */
interface WebPushService
{
    public function send(
        NotificationInterface $notification,
        SubscriptionInterface $subscription
    ): StatusReportInterface;
}
