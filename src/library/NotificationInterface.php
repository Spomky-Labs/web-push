<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2020-2021 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

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
