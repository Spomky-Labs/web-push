<?php

declare(strict_types=1);

namespace WebPush\VAPID;

use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use WebPush\Exception\OperationException;
use WebPush\Extension;
use WebPush\Loggable;
use WebPush\NotificationInterface;
use WebPush\RequestData;
use WebPush\SubscriptionInterface;
use function is_array;
use function parse_url;
use function sprintf;

final class VAPIDExtension implements Extension, Loggable
{
    private string $tokenExpirationTime = 'now +1hour';

    private LoggerInterface $logger;

    public function __construct(
        private readonly string $subject,
        private readonly JWSProvider $jwsProvider,
        private readonly ClockInterface $clock
    ) {
        $this->logger = new NullLogger();
    }

    public static function create(string $subject, JWSProvider $jwsProvider, ClockInterface $clock): self
    {
        return new self($subject, $jwsProvider, $clock);
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function setTokenExpirationTime(string $tokenExpirationTime): self
    {
        $this->tokenExpirationTime = $tokenExpirationTime;

        return $this;
    }

    public function process(
        RequestData $requestData,
        NotificationInterface $notification,
        SubscriptionInterface $subscription
    ): void {
        $this->logger->debug('Processing with VAPID header');
        $endpoint = $subscription->getEndpoint();
        $expiresAt = $this->clock->now()
            ->modify($this->tokenExpirationTime);
        $parsedEndpoint = parse_url($endpoint);
        if (! is_array($parsedEndpoint) || ! isset($parsedEndpoint['host'], $parsedEndpoint['scheme'])) {
            throw new OperationException('Invalid subscription endpoint');
        }
        $origin = $parsedEndpoint['scheme'] . '://' . $parsedEndpoint['host'] . (isset($parsedEndpoint['port']) ? ':' . $parsedEndpoint['port'] : '');
        $claims = [
            'aud' => $origin,
            'sub' => $this->subject,
            'exp' => $expiresAt->getTimestamp(),
        ];

        $this->logger->debug('Trying to get the header from the cache');
        $header = $this->jwsProvider->computeHeader($claims);
        $this->logger->debug('Header from cache', [
            'header' => $header,
        ]);

        $requestData
            ->addHeader('Authorization', sprintf('vapid t=%s, k=%s', $header->getToken(), $header->getKey()))
        ;
    }
}
