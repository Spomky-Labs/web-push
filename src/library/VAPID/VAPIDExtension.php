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

use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Safe\DateTimeImmutable;
use function Safe\parse_url;
use function Safe\sprintf;
use WebPush\Cachable;
use WebPush\Extension;
use WebPush\Loggable;
use WebPush\Notification;
use WebPush\Subscription;

class VAPIDExtension implements Extension, Loggable, Cachable
{
    private JWSProvider $jwsProvider;
    private ?CacheItemPoolInterface $cache = null;
    private string $tokenExpirationTime = 'now +1hour';
    private LoggerInterface $logger;
    private string $subject;
    private string $cacheExpirationTime = 'now +30min';

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

    public function setCache(CacheItemPoolInterface $cache, string $cacheExpirationTime = 'now +30min'): self
    {
        $this->cache = $cache;
        $this->cacheExpirationTime = $cacheExpirationTime;

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
        if (null !== $this->cache) {
            $this->logger->debug('Caching feature is available');
            $header = $this->getHeaderFromCache($origin, $claims);
            $this->logger->debug('Header from cache', ['header' => $header]);
        } else {
            $this->logger->debug('Caching feature is not available');
            $header = $this->jwsProvider->computeHeader($claims);
            $this->logger->debug('Generated header', ['header' => $header]);
        }

        return $request
            ->withAddedHeader('Authorization', sprintf('vapid t=%s, k=%s', $header->getToken(), $header->getKey()))
        ;
    }

    /**
     * @param array<string, mixed> $claims
     */
    private function getHeaderFromCache(string $origin, array $claims): Header
    {
        $jwsProvider = $this->jwsProvider;
        $computedCacheKey = hash('sha512', $origin);

        $item = $this->cache->getItem($computedCacheKey);
        if ($item->isHit()) {
            return $item->get();
        }

        $token = $jwsProvider->computeHeader($claims);
        $item = $item
            ->set($token)
            ->expiresAt(new DateTimeImmutable($this->cacheExpirationTime))
        ;
        $this->cache->save($item);

        return $token;
    }
}
