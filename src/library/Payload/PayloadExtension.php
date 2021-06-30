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

namespace WebPush\Payload;

use function array_key_exists;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function Safe\sprintf;
use WebPush\Extension;
use WebPush\Loggable;
use WebPush\NotificationInterface;
use WebPush\SubscriptionInterface;

class PayloadExtension implements Extension, Loggable
{
    /**
     * @var ContentEncoding[]
     */
    private array $contentEncodings = [];
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

    public function addContentEncoding(ContentEncoding $contentEncoding): self
    {
        $this->contentEncodings[$contentEncoding->name()] = $contentEncoding;

        return $this;
    }

    public function process(RequestInterface $request, NotificationInterface $notification, SubscriptionInterface $subscription): RequestInterface
    {
        $this->logger->debug('Processing with payload');
        $payload = $notification->getPayload();
        if (null === $payload || '' === $payload) {
            $this->logger->debug('No payload');

            return $request
                ->withAddedHeader('Content-Length', '0')
            ;
        }

        $encoder = $this->findEncoder($subscription);
        $this->logger->debug(sprintf('Encoder found: %s. Processing with the encoder.', $encoder->name()));

        $request = $request
            ->withAddedHeader('Content-Type', 'application/octet-stream')
            ->withAddedHeader('Content-Encoding', $encoder->name())
        ;

        return $encoder->encode($payload, $request, $subscription);
    }

    private function findEncoder(SubscriptionInterface $subscription): ContentEncoding
    {
        $supportedContentEncodings = $subscription->getSupportedContentEncodings();
        foreach ($supportedContentEncodings as $supportedContentEncoding) {
            if (array_key_exists($supportedContentEncoding, $this->contentEncodings)) {
                return $this->contentEncodings[$supportedContentEncoding];
            }
        }
        throw new InvalidArgumentException(sprintf('No content encoding found. Supported content encodings for the subscription are: %s', implode(', ', $supportedContentEncodings)));
    }
}
