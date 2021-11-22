<?php

declare(strict_types=1);

namespace WebPush\Bundle\Service;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use WebPush\ExtensionManager;
use WebPush\Loggable;
use WebPush\NotificationInterface;
use WebPush\SubscriptionInterface;
use WebPush\WebPushService;

class WebPush implements WebPushService, Loggable
{
    private LoggerInterface $logger;

    public function __construct(
        private HttpClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        private ExtensionManager $extensionManager
    ) {
        $this->logger = new NullLogger();
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function send(NotificationInterface $notification, SubscriptionInterface $subscription): StatusReport
    {
        $this->logger->debug('Sending notification', [
            'notification' => $notification,
            'subscription' => $subscription,
        ]);
        $request = $this->requestFactory->createRequest('POST', $subscription->getEndpoint());
        $request = $this->extensionManager->process($request, $notification, $subscription);
        $this->logger->debug('Request ready', [
            'request' => $request,
        ]);

        $response = $this->client->request('POST', $subscription->getEndpoint(), [
            'body' => $request->getBody()
                ->getContents(),
            'headers' => $request->getHeaders(),
        ]);
        $this->logger->debug('Response received', [
            'response' => $response,
        ]);

        return new StatusReport($subscription, $notification, $response);
    }
}
