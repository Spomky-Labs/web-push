<?php

declare(strict_types=1);

namespace WebPush;

use function count;
use function sprintf;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

final readonly class StatusReport implements StatusReportInterface
{
    /**
     * @param string[] $links
     */
    public function __construct(
        private SubscriptionInterface $subscription,
        private NotificationInterface $notification,
        private int $code,
        private string $location,
        private array $links
    ) {
    }

    /**
     * @param string[] $links
     */
    public static function create(
        SubscriptionInterface $subscription,
        NotificationInterface $notification,
        int $code,
        string $location,
        array $links
    ): self {
        return new self($subscription, $notification, $code, $location, $links);
    }

    public static function createFromResponse(
        SubscriptionInterface $subscription,
        NotificationInterface $notification,
        ResponseInterface $response
    ): self {
        $code = $response->getStatusCode();
        $headers = $response->getHeaders(false);
        $location = implode(', ', $headers['location'] ?? ['']);
        $links = $headers['link'] ?? [];

        return new self($subscription, $notification, $code, $location, $links);
    }

    /**
     * Create a failed StatusReport from an exception (e.g., for network errors).
     *
     * @param SubscriptionInterface $subscription The subscription that failed
     * @param NotificationInterface $notification The notification that failed to send
     * @param Throwable $exception The exception that occurred
     * @return self A StatusReport with a 0 status code indicating a transport/network error
     */
    public static function createFromException(
        SubscriptionInterface $subscription,
        NotificationInterface $notification,
        Throwable $exception
    ): self {
        // Use status code 0 to indicate a transport/network error (no HTTP response received)
        return new self($subscription, $notification, 0, '', []);
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
        return $this->code >= 200 && $this->code < 300;
    }

    public function isSubscriptionExpired(): bool
    {
        return $this->code === 404 || $this->code === 410;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @return string[]
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * Get the HTTP status code of the response.
     *
     * @return int The HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->code;
    }

    /**
     * Check if the request was rate limited (HTTP 429).
     *
     * @return bool True if rate limited, false otherwise
     */
    public function isRateLimited(): bool
    {
        return $this->code === 429;
    }

    /**
     * Check if the error is retryable (5xx server errors or rate limit 429).
     *
     * @return bool True if the request should be retried, false otherwise
     */
    public function isRetryable(): bool
    {
        return $this->code === 429 || ($this->code >= 500 && $this->code < 600);
    }

    /**
     * Check if the response is a server error (5xx).
     *
     * @return bool True if server error, false otherwise
     */
    public function isServerError(): bool
    {
        return $this->code >= 500 && $this->code < 600;
    }

    /**
     * Check if the response is a client error (4xx).
     *
     * @return bool True if client error, false otherwise
     */
    public function isClientError(): bool
    {
        return $this->code >= 400 && $this->code < 500;
    }

    /**
     * Check if the error is a transport/network error (no HTTP response received).
     *
     * @return bool True if transport error, false otherwise
     */
    public function isTransportError(): bool
    {
        return $this->code === 0;
    }

    /**
     * Get a human-readable error message based on the status code.
     *
     * @return string|null Error message, or null if successful
     */
    public function getErrorMessage(): ?string
    {
        if ($this->isSuccess()) {
            return null;
        }

        return match ($this->code) {
            0 => 'Transport error: Failed to connect to the push service',
            400 => 'Bad request: The request was malformed or invalid',
            401 => 'Unauthorized: VAPID authentication failed',
            404 => 'Not found: The subscription endpoint no longer exists',
            410 => 'Gone: The subscription has expired',
            413 => 'Payload too large: The notification payload exceeds the size limit',
            429 => 'Too many requests: Rate limit exceeded',
            500 => 'Internal server error: The push service encountered an error',
            502 => 'Bad gateway: The push service is temporarily unavailable',
            503 => 'Service unavailable: The push service is temporarily overloaded',
            default => sprintf('HTTP error %d', $this->code),
        };
    }

    /**
     * Filter reports to get only successful ones.
     *
     * @param StatusReportInterface[] $reports
     * @return StatusReportInterface[]
     */
    public static function filterSuccessful(array $reports): array
    {
        return array_values(array_filter($reports, static fn (StatusReportInterface $r) => $r->isSuccess()));
    }

    /**
     * Filter reports to get only failed ones.
     *
     * @param StatusReportInterface[] $reports
     * @return StatusReportInterface[]
     */
    public static function filterFailed(array $reports): array
    {
        return array_values(array_filter($reports, static fn (StatusReportInterface $r) => ! $r->isSuccess()));
    }

    /**
     * Filter reports to get only those with expired subscriptions.
     *
     * @param StatusReportInterface[] $reports
     * @return StatusReportInterface[]
     */
    public static function filterExpired(array $reports): array
    {
        return array_values(
            array_filter($reports, static fn (StatusReportInterface $r) => $r->isSubscriptionExpired())
        );
    }

    /**
     * Filter reports to get only retryable errors.
     *
     * @param StatusReportInterface[] $reports
     * @return StatusReportInterface[]
     */
    public static function filterRetryable(array $reports): array
    {
        return array_values(array_filter($reports, static fn (StatusReportInterface $r) => $r->isRetryable()));
    }

    /**
     * Get statistics about a collection of reports.
     *
     * @param StatusReportInterface[] $reports
     * @return array{total: int, successful: int, failed: int, expired: int, retryable: int}
     */
    public static function getStatistics(array $reports): array
    {
        $total = count($reports);
        $successful = count(self::filterSuccessful($reports));
        $failed = count(self::filterFailed($reports));
        $expired = count(self::filterExpired($reports));
        $retryable = count(self::filterRetryable($reports));

        return [
            'total' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'expired' => $expired,
            'retryable' => $retryable,
        ];
    }
}
