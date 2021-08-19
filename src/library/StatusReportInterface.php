<?php

declare(strict_types=1);

namespace WebPush;

interface StatusReportInterface
{
    public function getSubscription(): SubscriptionInterface;

    public function getNotification(): NotificationInterface;

    public function isSuccess(): bool;

    public function isSubscriptionExpired(): bool;

    public function getLocation(): string;

    public function getLinks(): array;
}
