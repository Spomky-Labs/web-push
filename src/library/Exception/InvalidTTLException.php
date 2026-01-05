<?php

declare(strict_types=1);

namespace WebPush\Exception;

/**
 * Exception thrown when a notification TTL (Time-To-Live) is invalid.
 */
class InvalidTTLException extends ValidationException
{
    public function __construct(
        string $message,
        public readonly int $ttl
    ) {
        parent::__construct($message);
    }
}
