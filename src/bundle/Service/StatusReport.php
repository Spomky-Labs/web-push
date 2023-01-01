<?php

declare(strict_types=1);

namespace WebPush\Bundle\Service;

use Symfony\Contracts\HttpClient\ResponseInterface;
use WebPush\NotificationInterface;
use WebPush\StatusReportInterface;
use WebPush\SubscriptionInterface;

final class StatusReport implements StatusReportInterface
{
    private ?int $code = null;

    private ?string $location = null;

    private ?array $links = null;

    public function __construct(
        private readonly SubscriptionInterface $subscription,
        private readonly NotificationInterface $notification,
        private readonly ResponseInterface $response
    ) {
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

        return $code === 404 || $code === 410;
    }

    public function getLocation(): string
    {
        if ($this->location === null) {
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
        if ($this->links === null) {
            $headers = $this->response->getHeaders();
            $this->links = $headers['link'] ?? [];
        }

        return $this->links;
    }

    private function prepareStatusCode(): int
    {
        if ($this->code === null) {
            $this->code = $this->response->getStatusCode();
        }

        return $this->code;
    }
}
