<?php

declare(strict_types=1);

namespace WebPush;

use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class TopicExtension implements Extension, Loggable
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
        $topic = $notification->getTopic();
        $this->logger->debug('Processing with the Topic extension', [
            'Topic' => $topic,
        ]);
        if ($topic === null) {
            return $request;
        }

        return $request
            ->withAddedHeader('Topic', $topic)
        ;
    }
}
