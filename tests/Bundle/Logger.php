<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle;

use function array_key_exists;
use Psr\Log\LoggerInterface;
use Stringable;

/**
 * @internal
 */
final class Logger implements LoggerInterface
{
    private array $messages = [];

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        if (! array_key_exists($level, $this->messages)) {
            $this->messages[$level] = [];
        }
        $this->messages[$level][] = [
            'msg' => $message,
            'ctx' => $context,
        ];
    }
}
