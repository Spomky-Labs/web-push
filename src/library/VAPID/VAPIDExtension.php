<?php

declare(strict_types=1);

namespace WebPush\VAPID;

use DateTimeImmutable;
use Exception;
use function is_array;
use function parse_url;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function sprintf;
use WebPush\Exception\OperationException;
use WebPush\Extension;
use WebPush\Loggable;
use WebPush\NotificationInterface;
use WebPush\SubscriptionInterface;

class VAPIDExtension implements Extension, Loggable
{
    private JWSProvider $jwsProvider;
    private string $tokenExpirationTime = 'now +1hour';
    private LoggerInterface $logger;
    private string $subject;

    public function __construct(string $subject, JWSProvider $jwsProvider)
    {
        $this->subject = $subject;
        $this->jwsProvider = $jwsProvider;
        $this->logger = new NullLogger();
    }

    public static function create(string $subject, JWSProvider $jwsProvider): self
    {
        return new self($subject, $jwsProvider);
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

    /**
     * @throws Exception
     */
    public function process(RequestInterface $request, NotificationInterface $notification, SubscriptionInterface $subscription): RequestInterface
    {
        $this->logger->debug('Processing with VAPID header');
        $endpoint = $subscription->getEndpoint();
        $expiresAt = new DateTimeImmutable($this->tokenExpirationTime);
        $parsedEndpoint = parse_url($endpoint);
        if (!is_array($parsedEndpoint) || !isset($parsedEndpoint['host'], $parsedEndpoint['scheme'])) {
            throw new OperationException('Invalid subscription endpoint');
        }
        $origin = $parsedEndpoint['scheme'].'://'.$parsedEndpoint['host'].(isset($parsedEndpoint['port']) ? ':'.$parsedEndpoint['port'] : '');
        $claims = [
            'aud' => $origin,
            'sub' => $this->subject,
            'exp' => $expiresAt->getTimestamp(),
        ];

        $this->logger->debug('Trying to get the header from the cache');
        $header = $this->jwsProvider->computeHeader($claims);
        $this->logger->debug('Header from cache', ['header' => $header]);

        return $request
            ->withAddedHeader('Authorization', sprintf('vapid t=%s, k=%s', $header->getToken(), $header->getKey()))
        ;
    }
}
