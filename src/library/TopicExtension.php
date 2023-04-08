<?php

declare(strict_types=1);

namespace WebPush;

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
        RequestData $requestData,
        NotificationInterface $notification,
        SubscriptionInterface $subscription
    ): void {
        $topic = $notification->getTopic();
        $this->logger->debug('Processing with the Topic extension', [
            'Topic' => $topic,
        ]);
        if ($topic === null) {
            return;
        }

        $requestData
            ->addHeader('Topic', $topic)
        ;
    }
}
