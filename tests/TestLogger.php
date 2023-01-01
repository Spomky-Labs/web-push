<?php

declare(strict_types=1);

namespace WebPush\Tests;

use BadMethodCallException;
use function call_user_func;
use function call_user_func_array;
use function is_string;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Used for testing purposes.
 *
 * It records all records and gives you access to them for verification.
 *
 * @method bool hasEmergency($record)
 * @method bool hasAlert($record)
 * @method bool hasCritical($record)
 * @method bool hasError($record)
 * @method bool hasWarning($record)
 * @method bool hasNotice($record)
 * @method bool hasInfo($record)
 * @method bool hasDebug($record)
 * @method bool hasEmergencyRecords()
 * @method bool hasAlertRecords()
 * @method bool hasCriticalRecords()
 * @method bool hasErrorRecords()
 * @method bool hasWarningRecords()
 * @method bool hasNoticeRecords()
 * @method bool hasInfoRecords()
 * @method bool hasDebugRecords()
 * @method bool hasEmergencyThatContains($message)
 * @method bool hasAlertThatContains($message)
 * @method bool hasCriticalThatContains($message)
 * @method bool hasErrorThatContains($message)
 * @method bool hasWarningThatContains($message)
 * @method bool hasNoticeThatContains($message)
 * @method bool hasInfoThatContains($message)
 * @method bool hasDebugThatContains($message)
 * @method bool hasEmergencyThatMatches($message)
 * @method bool hasAlertThatMatches($message)
 * @method bool hasCriticalThatMatches($message)
 * @method bool hasErrorThatMatches($message)
 * @method bool hasWarningThatMatches($message)
 * @method bool hasNoticeThatMatches($message)
 * @method bool hasInfoThatMatches($message)
 * @method bool hasDebugThatMatches($message)
 * @method bool hasEmergencyThatPasses($message)
 * @method bool hasAlertThatPasses($message)
 * @method bool hasCriticalThatPasses($message)
 * @method bool hasErrorThatPasses($message)
 * @method bool hasWarningThatPasses($message)
 * @method bool hasNoticeThatPasses($message)
 * @method bool hasInfoThatPasses($message)
 * @method bool hasDebugThatPasses($message)
 * @internal
 */
final class TestLogger implements LoggerInterface
{
    public array $records = [];

    public array $recordsByLevel = [];

    public function __call($method, $args)
    {
        if (preg_match(
            '/(.*)(Debug|Info|Notice|Warning|Error|Critical|Alert|Emergency)(.*)/',
            (string) $method,
            $matches
        ) > 0) {
            $genericMethod = $matches[1] . ($matches[3] !== 'Records' ? 'Record' : '') . $matches[3];
            $level = mb_strtolower($matches[2]);
            if (method_exists($this, $genericMethod)) {
                $args[] = $level;

                return call_user_func_array([$this, $genericMethod], $args);
            }
        }
        throw new BadMethodCallException('Call to undefined method ' . static::class . '::' . $method . '()');
    }

    /**
     * @inheritdoc
     */
    public function log($level, $message, array $context = []): void
    {
        $record = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];

        $this->recordsByLevel[$record['level']][] = $record;
        $this->records[] = $record;
    }

    public function hasRecords($level): bool
    {
        return isset($this->recordsByLevel[$level]);
    }

    public function hasRecord($record, $level): bool
    {
        if (is_string($record)) {
            $record = [
                'message' => $record,
            ];
        }

        return $this->hasRecordThatPasses(function ($rec) use ($record) {
            if ($rec['message'] !== $record['message']) {
                return false;
            }
            if (isset($record['context']) && $rec['context'] !== $record['context']) {
                return false;
            }

            return true;
        }, $level);
    }

    public function hasRecordThatPasses(callable $predicate, $level): bool
    {
        if (! isset($this->recordsByLevel[$level])) {
            return false;
        }
        foreach ($this->recordsByLevel[$level] as $i => $rec) {
            if (call_user_func($predicate, $rec, $i)) {
                return true;
            }
        }

        return false;
    }

    public function reset(): void
    {
        $this->records = [];
        $this->recordsByLevel = [];
    }

    /**
     * System is unusable.
     *
     * @param mixed[] $context
     */
    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.
     *
     * @param mixed[] $context
     */
    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param mixed[] $context
     */
    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically be logged and monitored.
     *
     * @param mixed[] $context
     */
    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
     *
     * @param mixed[] $context
     */
    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param mixed[] $context
     */
    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param mixed[] $context
     */
    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param mixed[] $context
     */
    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
}
