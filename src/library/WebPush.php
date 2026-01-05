<?php

declare(strict_types=1);

namespace WebPush;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use WebPush\Exception\OperationException;

final class WebPush implements WebPushService, Loggable
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly ExtensionManager $extensionManager
    ) {
        $this->logger = new NullLogger();
    }

    public static function create(HttpClientInterface $client, ExtensionManager $extensionManager): self
    {
        return new self($client, $extensionManager);
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function send(
        NotificationInterface $notification,
        SubscriptionInterface $subscription
    ): StatusReportInterface {
        $this->logger->debug('Sending notification', [
            'endpoint' => $subscription->getEndpoint(),
            'ttl' => $notification->getTTL(),
            'urgency' => $notification->getUrgency(),
            'topic' => $notification->getTopic(),
        ]);

        try {
            $requestData = $this->extensionManager->process($notification, $subscription);
            $this->logger->debug('Request data ready', [
                'endpoint' => $subscription->getEndpoint(),
                'headers' => $this->filterSensitiveHeaders($requestData->getHeaders()),
            ]);

            $response = $this->client->request(
                'POST',
                $subscription->getEndpoint(),
                [
                    'headers' => $requestData->getHeaders(),
                    'body' => $requestData->getBody(),
                ]
            );

            $statusCode = $response->getStatusCode();
            $this->logger->debug('Response received', [
                'endpoint' => $subscription->getEndpoint(),
                'status_code' => $statusCode,
            ]);

            return StatusReport::createFromResponse($subscription, $notification, $response);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Transport error while sending notification', [
                'endpoint' => $subscription->getEndpoint(),
                'error' => $e->getMessage(),
            ]);
            throw new OperationException(
                sprintf('Failed to send notification to %s: %s', $subscription->getEndpoint(), $e->getMessage()),
                0,
                $e
            );
        } catch (HttpExceptionInterface $e) {
            $this->logger->error('HTTP error while sending notification', [
                'endpoint' => $subscription->getEndpoint(),
                'status_code' => $e->getResponse()->getStatusCode(),
                'error' => $e->getMessage(),
            ]);
            throw new OperationException(
                sprintf(
                    'HTTP error %d while sending notification to %s: %s',
                    $e->getResponse()->getStatusCode(),
                    $subscription->getEndpoint(),
                    $e->getMessage()
                ),
                0,
                $e
            );
        }
    }

    /**
     * Send a notification to multiple subscriptions.
     *
     * Unlike send(), this method does not throw exceptions for individual failures.
     * Instead, it attempts to send to all subscriptions and returns a StatusReport
     * for each one, allowing you to inspect successes and failures.
     *
     * @param Subscription[] $subscriptions
     * @return StatusReport[]
     */
    public function sendToMultiple(NotificationInterface $notification, array $subscriptions): array
    {
        $reports = [];
        foreach ($subscriptions as $subscription) {
            try {
                $reports[] = $this->send($notification, $subscription);
            } catch (OperationException $e) {
                $this->logger->warning('Failed to send notification in batch', [
                    'endpoint' => $subscription->getEndpoint(),
                    'error' => $e->getMessage(),
                ]);
                // Create a failed status report instead of propagating the exception
                $reports[] = StatusReport::createFromException($subscription, $notification, $e);
            }
        }
        return $reports;
    }

    /**
     * Filter sensitive data from headers for logging purposes.
     *
     * @param array<string, mixed> $headers
     * @return array<string, mixed>
     */
    private function filterSensitiveHeaders(array $headers): array
    {
        $filtered = $headers;

        // Filter Authorization header (contains VAPID token)
        if (isset($filtered['Authorization'])) {
            $filtered['Authorization'] = '[FILTERED]';
        }

        // Filter Crypto-Key header (contains encryption keys)
        if (isset($filtered['Crypto-Key'])) {
            $filtered['Crypto-Key'] = '[FILTERED]';
        }

        return $filtered;
    }
}
