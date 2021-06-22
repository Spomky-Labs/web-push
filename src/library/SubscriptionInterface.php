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

use JsonSerializable;

interface SubscriptionInterface extends JsonSerializable
{
    public function getKeys(): array;

    public function hasKey(string $key): bool;

    public function getKey(string $key): string;

    public function getExpirationTime(): ?int;

    public function getEndpoint(): string;

    /**
     * @return string[]
     */
    public function getSupportedContentEncodings(): array;

    /**
     * @return array<string, string|string[]>
     */
    public function jsonSerialize(): array;
}
