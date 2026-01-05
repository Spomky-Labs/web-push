<?php

declare(strict_types=1);

namespace WebPush;

/**
 * @method array<StatusReportInterface> sendToMultiple(NotificationInterface $notification, array $subscriptions)
 */
interface WebPushService
{
    public function send(
        NotificationInterface $notification,
        SubscriptionInterface $subscription
    ): StatusReportInterface;
}
