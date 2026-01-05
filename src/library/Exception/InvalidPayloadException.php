<?php

declare(strict_types=1);

namespace WebPush\Exception;

/**
 * Exception thrown when a notification payload is invalid.
 */
class InvalidPayloadException extends ValidationException
{
    public function __construct(
        string $message,
        public readonly int $size
    ) {
        parent::__construct($message);
    }
}
