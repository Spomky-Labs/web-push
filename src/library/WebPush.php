<?php

declare(strict_types=1);

namespace WebPush;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
            'notification' => $notification,
            'subscription' => $subscription,
        ]);
        $requestData = $this->extensionManager->process($notification, $subscription);
        $this->logger->debug('Request data ready', [
            'requestData' => $requestData,
        ]);

        $response = $this->client->request(
            'POST',
            $subscription->getEndpoint(),
            [
                'headers' => $requestData->getHeaders(),
                'body' => $requestData->getBody(),
            ]
        );
        $this->logger->debug('Response received', [
            'response' => $response,
        ]);

        return StatusReport::createFromResponse($subscription, $notification, $response);
    }
}
