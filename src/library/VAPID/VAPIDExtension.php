<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2020 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace WebPush\VAPID;

use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Safe\DateTimeImmutable;
use function Safe\parse_url;
use function Safe\sprintf;
use WebPush\Extension;
use WebPush\Loggable;
use WebPush\Notification;
use WebPush\Subscription;

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

    public function process(RequestInterface $request, Notification $notification, Subscription $subscription): RequestInterface
    {
        $this->logger->debug('Processing with VAPID header');
        $endpoint = $subscription->getEndpoint();
        $expiresAt = new DateTimeImmutable($this->tokenExpirationTime);
        $parsedEndpoint = parse_url($endpoint);
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
