<?php

declare(strict_types=1);

namespace WebPush;

interface NotificationInterface
{
    /**
     * Urgency levels for notifications.
     */
    public const URGENCY_VERY_LOW = 'very-low';

    public const URGENCY_LOW = 'low';

    public const URGENCY_NORMAL = 'normal';

    public const URGENCY_HIGH = 'high';

    /**
     * Common TTL (Time-To-Live) values in seconds.
     */
    public const TTL_IMMEDIATE = 0; // Deliver immediately or not at all

    public const TTL_ONE_MINUTE = 60;

    public const TTL_FIVE_MINUTES = 300;

    public const TTL_TEN_MINUTES = 600;

    public const TTL_ONE_HOUR = 3600;

    public const TTL_ONE_DAY = 86400;

    public const TTL_ONE_WEEK = 604800;

    public const TTL_FOUR_WEEKS = 2419200;

    public function getUrgency(): string;

    public function getPayload(): ?string;

    public function getTopic(): ?string;

    public function getTTL(): int;

    public function isAsync(): bool;

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array;

    public function has(string $key): bool;

    public function get(string $key): mixed;
}
