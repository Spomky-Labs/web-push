<?php

declare(strict_types=1);

namespace WebPush;

interface NotificationInterface
{
    public const URGENCY_VERY_LOW = 'very-low';
    public const URGENCY_LOW = 'low';
    public const URGENCY_NORMAL = 'normal';
    public const URGENCY_HIGH = 'high';

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

    /**
     * @return mixed
     */
    public function get(string $key);
}
