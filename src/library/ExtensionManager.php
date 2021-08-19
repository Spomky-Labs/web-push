<?php

declare(strict_types=1);

namespace WebPush;

use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ExtensionManager implements Loggable
{
    /**
     * @var Extension[]
     */
    private array $extensions = [];
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

    public function add(Extension $extension): self
    {
        $this->extensions[] = $extension;
        $this->logger->debug('Extension added', ['extension' => $extension]);

        return $this;
    }

    public function process(RequestInterface $request, NotificationInterface $notification, SubscriptionInterface $subscription): RequestInterface
    {
        $this->logger->debug('Processing the request');
        foreach ($this->extensions as $extension) {
            $request = $extension->process($request, $notification, $subscription);
        }
        $this->logger->debug('Processing done');

        return $request;
    }
}
