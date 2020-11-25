<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2020 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace WebPush;

use JsonSerializable;
use function Safe\json_encode;
use function Safe\ksort;

/**
 * @see https://notifications.spec.whatwg.org/#actions
 */
class Action implements JsonSerializable
{
    private string $action;
    private string $title;
    private ?string $icon = null;

    public function __construct(string $action, string $title)
    {
        $this->action = $action;
        $this->title = $title;
    }

    public function toString(): string
    {
        return json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function create(string $action, string $title): self
    {
        return new self($action, $title);
    }

    public function withIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $r = array_filter(get_object_vars($this), static function ($v): bool {
            return null !== $v;
        });

        ksort($r);

        return $r;
    }
}
