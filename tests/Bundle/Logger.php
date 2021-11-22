<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle;

use function array_key_exists;
use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{
    private array $messages = [];

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function emergency($message, array $context = [])
    {
        $this->log('emergency', $message, $context);
    }

    public function alert($message, array $context = [])
    {
        $this->log('alert', $message, $context);
    }

    public function critical($message, array $context = [])
    {
        $this->log('critical', $message, $context);
    }

    public function error($message, array $context = [])
    {
        $this->log('error', $message, $context);
    }

    public function warning($message, array $context = [])
    {
        $this->log('warning', $message, $context);
    }

    public function notice($message, array $context = [])
    {
        $this->log('notice', $message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->log('info', $message, $context);
    }

    public function debug($message, array $context = [])
    {
        $this->log('debug', $message, $context);
    }

    public function log($level, $message, array $context = [])
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
