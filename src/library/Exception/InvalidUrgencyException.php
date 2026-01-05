<?php

declare(strict_types=1);

namespace WebPush\Exception;

/**
 * Exception thrown when a notification urgency is invalid.
 */
class InvalidUrgencyException extends ValidationException
{
    public function __construct(
        string $message,
        public readonly string $urgency
    ) {
        parent::__construct($message);
    }
}
