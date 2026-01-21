<?php

declare(strict_types=1);

namespace WebPush\Payload;

use function array_key_exists;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function sprintf;
use WebPush\Extension;
use WebPush\Loggable;
use WebPush\NotificationInterface;
use WebPush\RequestData;
use WebPush\SubscriptionInterface;

final class PayloadExtension implements Extension, Loggable
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

    public function process(
        RequestData $requestData,
        NotificationInterface $notification,
        SubscriptionInterface $subscription
    ): void {
        $this->logger->debug('Processing with payload');
        $payload = $notification->getPayload();
        if ($payload === null || $payload === '') {
            $this->logger->debug('No payload');
            $requestData->addHeader('Content-Length', '0');

            return;
        }

        $encoder = $this->findEncoder($subscription);
        $this->logger->debug(sprintf('Encoder found: %s. Processing with the encoder.', $encoder->name()));

        $requestData
            ->addHeader('Content-Type', 'application/octet-stream')
            ->addHeader('Content-Encoding', $encoder->name())
        ;

        $encoder->encode($payload, $requestData, $subscription);
    }

    private function findEncoder(SubscriptionInterface $subscription): ContentEncoding
    {
        $supportedContentEncodings = $subscription->getSupportedContentEncodings();
        foreach ($supportedContentEncodings as $supportedContentEncoding) {
            if (array_key_exists($supportedContentEncoding, $this->contentEncodings)) {
                return $this->contentEncodings[$supportedContentEncoding];
            }
        }
        throw new InvalidArgumentException(sprintf(
            'No content encoding found. Supported content encodings for the subscription are: %s',
            implode(', ', $supportedContentEncodings)
        ));
    }
}
