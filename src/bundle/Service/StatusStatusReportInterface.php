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

namespace WebPush\Bundle\Service;

use Symfony\Contracts\HttpClient\ResponseInterface;
use WebPush\NotificationInterface;
use WebPush\StatusReportInterface;
use WebPush\SubscriptionInterface;

class StatusStatusReportInterface implements StatusReportInterface
{
    private SubscriptionInterface $subscription;
    private NotificationInterface $notification;
    private ResponseInterface $response;
    private ?int $code = null;
    private ?string $location = null;
    private ?array $links = null;

    public function __construct(SubscriptionInterface $subscription, NotificationInterface $notification, ResponseInterface $response)
    {
        $this->subscription = $subscription;
        $this->notification = $notification;
        $this->response = $response;
    }

    public function getSubscription(): SubscriptionInterface
    {
        return $this->subscription;
    }

    public function getNotification(): NotificationInterface
    {
        return $this->notification;
    }

    public function isSuccess(): bool
    {
        if (null === $this->code) {
            $this->code = $this->response->getStatusCode();
        }

        return $this->code >= 200 && $this->code < 300;
    }

    public function isSubscriptionExpired(): bool
    {
        if (null === $this->code) {
            $this->code = $this->response->getStatusCode();
        }

        return 404 === $this->code || 410 === $this->code;
    }

    public function getLocation(): string
    {
        if (null === $this->location) {
            $headers = $this->response->getHeaders();
            $this->location = implode(', ', $headers['location'] ?? ['']);
        }

        return $this->location;
    }

    /**
     * @return string[]
     */
    public function getLinks(): array
    {
        if (null === $this->links) {
            $headers = $this->response->getHeaders();
            $this->links = $headers['link'] ?? '';
        }

        return $this->links;
    }
}
