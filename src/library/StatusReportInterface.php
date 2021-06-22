<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2020-2021 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

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
