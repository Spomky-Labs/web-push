<?php

declare(strict_types=1);

namespace WebPush;

/**
 * @method int getStatusCode() Get the HTTP status code of the response
 * @method bool isRateLimited() Check if the request was rate limited (HTTP 429)
 * @method bool isRetryable() Check if the error is retryable (5xx server errors or rate limit 429)
 * @method bool isServerError() Check if the response is a server error (5xx)
 * @method bool isClientError() Check if the response is a client error (4xx)
 * @method bool isTransportError() Check if the error is a transport/network error (no HTTP response received)
 * @method string|null getErrorMessage() Get a human-readable error message based on the status code
 */
interface StatusReportInterface
{
    public function getSubscription(): SubscriptionInterface;

    public function getNotification(): NotificationInterface;

    public function isSuccess(): bool;

    public function isSubscriptionExpired(): bool;

    public function getLocation(): string;

    /**
     * @return string[]
     */
    public function getLinks(): array;
}
