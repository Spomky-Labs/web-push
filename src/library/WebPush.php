<?php

declare(strict_types=1);

namespace WebPush;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class WebPush implements WebPushService, Loggable
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly ExtensionManager $extensionManager
    ) {
        $this->logger = new NullLogger();
    }

    public static function create(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        ExtensionManager $extensionManager
    ): self {
        return new self($client, $requestFactory, $extensionManager);
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
        $request = $this->requestFactory->createRequest('POST', $subscription->getEndpoint());
        $request = $this->extensionManager->process($request, $notification, $subscription);
        $this->logger->debug('Request ready', [
            'request' => $request,
        ]);

        $response = $this->client->sendRequest($request);
        $this->logger->debug('Response received', [
            'response' => $response,
        ]);

        return StatusReport::createFromResponse($subscription, $notification, $response);
    }
}
