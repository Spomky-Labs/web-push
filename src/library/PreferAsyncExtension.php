<?php

declare(strict_types=1);

namespace WebPush;

use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class PreferAsyncExtension implements Extension, Loggable
{
    private LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    public static function create(): self
    {
        return new self();
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function process(
        RequestInterface $request,
        NotificationInterface $notification,
        SubscriptionInterface $subscription
    ): RequestInterface {
        if (! $notification->isAsync()) {
            $this->logger->debug('Sending synchronous notification');

            return $request;
        }

        $this->logger->debug('Sending asynchronous notification');

        return $request
            ->withAddedHeader('Prefer', 'respond-async')
        ;
    }
}
