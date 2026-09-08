<?php

declare(strict_types=1);

namespace WebPush\Bundle\Service;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use WebPush\ExtensionManager;
use WebPush\Loggable;
use WebPush\NotificationInterface;
use WebPush\SubscriptionInterface;
use WebPush\WebPushService;

/**
 * @deprecated since 3.3.0, use WebPush\WebPush instead. Will be removed in 4.0.0.
 */
final class WebPush implements WebPushService, Loggable
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly ExtensionManager $extensionManager
    ) {
        $this->logger = new NullLogger();
        trigger_deprecation(
            'spomky-labs/web-push-bundle',
            '3.3.0',
            'The class "%s" is deprecated and will be removed in 4.0.0. Please use "%s" instead.',
            'WebPush\\Bundle\\Service\\WebPush',
            'WebPush\\WebPush'
        );
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
        $requestData = $this->extensionManager->process($notification, $subscription);
        $response = $this->client->request('POST', $subscription->getEndpoint(), [
            'body' => $requestData->getBody(),
            'headers' => $requestData->getHeaders(),
        ]);
        $this->logger->debug('Response received', [
            'response' => $response,
        ]);

        return new StatusReport($subscription, $notification, $response);
    }
}
