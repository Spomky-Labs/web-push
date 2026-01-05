<?php

declare(strict_types=1);

namespace WebPush\Exception;

/**
 * Exception thrown when a notification topic is invalid.
 */
class InvalidTopicException extends ValidationException
{
    public function __construct(
        string $message,
        public readonly string $topic
    ) {
        parent::__construct($message);
    }
}
