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

namespace WebPush;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class WebPush implements WebPushService, Loggable
{
    private ClientInterface $client;
    private LoggerInterface $logger;
    private RequestFactoryInterface $requestFactory;
    private ExtensionManager $extensionManager;

    public function __construct(ClientInterface $client, RequestFactoryInterface $requestFactory, ExtensionManager $extensionManager)
    {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->extensionManager = $extensionManager;
        $this->logger = new NullLogger();
    }

    public static function create(ClientInterface $client, RequestFactoryInterface $requestFactory, ExtensionManager $extensionManager): self
    {
        return new self($client, $requestFactory, $extensionManager);
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function send(Notification $notification, Subscription $subscription): StatusReport
    {
        $this->logger->debug('Sending notification', ['notification' => $notification, 'subscription' => $subscription]);
        $request = $this->requestFactory->createRequest('POST', $subscription->getEndpoint());
        $request = $this->extensionManager->process($request, $notification, $subscription);
        $this->logger->debug('Request ready', ['request' => $request]);

        $response = $this->client->sendRequest($request);
        $this->logger->debug('Response received', ['response' => $response]);

        return new StatusReport(
            $subscription,
            $notification,
            $request,
            $response
        );
    }
}
