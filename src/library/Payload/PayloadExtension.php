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

namespace WebPush\Payload;

use Assert\Assertion;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function Safe\sprintf;
use WebPush\Extension;
use WebPush\Loggable;
use WebPush\Notification;
use WebPush\Subscription;

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

    public function process(RequestInterface $request, Notification $notification, Subscription $subscription): RequestInterface
    {
        $this->logger->debug('Processing with payload');
        $payload = $notification->getPayload();
        if (null === $payload || '' === $payload) {
            $this->logger->debug('No payload');

            return $request
                ->withHeader('Content-Length', '0')
            ;
        }

        $contentEncoding = $subscription->getContentEncoding();
        Assertion::keyExists($this->contentEncodings, $contentEncoding, sprintf('The content encoding "%s" is not supported', $contentEncoding));
        $encoder = $this->contentEncodings[$contentEncoding];
        $this->logger->debug(sprintf('Encoder found: %s. Processing with the encoder.', $contentEncoding));

        $request = $request
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Encoding', $contentEncoding)
        ;

        return $encoder->encode($payload, $request, $subscription);
    }
}
