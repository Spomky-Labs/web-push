<?php

declare(strict_types=1);

namespace WebPush\Bundle\Service;

use Symfony\Contracts\HttpClient\ResponseInterface;
use WebPush\NotificationInterface;
use WebPush\StatusReportInterface;
use WebPush\SubscriptionInterface;

class StatusReport implements StatusReportInterface
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
        $code = $this->prepareStatusCode();

        return $code >= 200 && $code < 300;
    }

    public function isSubscriptionExpired(): bool
    {
        $code = $this->prepareStatusCode();

        return 404 === $code || 410 === $code;
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
            $this->links = $headers['link'] ?? [];
        }

        return $this->links;
    }

    private function prepareStatusCode(): int
    {
        if (null === $this->code) {
            $this->code = $this->response->getStatusCode();
        }

        return $this->code;
    }
}
