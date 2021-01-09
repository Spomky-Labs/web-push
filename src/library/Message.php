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

use function count;
use function is_array;
use JsonSerializable;
use function Safe\json_encode;
use function Safe\ksort;

/**
 * @see https://notifications.spec.whatwg.org/#notifications
 * @see https://developer.mozilla.org/en-US/docs/Web/API/Notification/Notification
 */
class Message implements JsonSerializable
{
    /**
     * @var Action[]
     */
    private array $actions = [];

    private string $body;

    /**
     * @var mixed|null
     */
    private $data;

    private ?string $dir = null; // = auto

    private ?string $badge = null;
    private ?string $icon = null;
    private ?string $image = null;
    private ?string $lang = null;
    private ?bool $renotify = null;
    private ?bool $requireInteraction = null;
    private ?bool $silent = null;
    private ?string $tag = null;
    private ?int $timestamp = null;

    /**
     * @var array<int, int>|null
     */
    private ?array $vibrate = null;

    public function __construct(string $body)
    {
        $this->body = $body;
    }

    public function toString(): string
    {
        return json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function create(string $body): self
    {
        return new self($body);
    }

    /**
     * @return array<int, Action>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return mixed|null
     */
    public function getData()
    {
        return $this->data;
    }

    public function getDir(): ?string
    {
        return $this->dir;
    }

    public function getBadge(): ?string
    {
        return $this->badge;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function getRenotify(): ?bool
    {
        return $this->renotify;
    }

    public function isInteractionRequired(): ?bool
    {
        return $this->requireInteraction;
    }

    public function isSilent(): ?bool
    {
        return $this->silent;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function getTimestamp(): ?int
    {
        return $this->timestamp;
    }

    /**
     * @return array<int, int>|null
     */
    public function getVibrate(): ?array
    {
        return $this->vibrate;
    }

    public function addAction(Action $action): self
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * @param mixed|null $data
     */
    public function withData($data): self
    {
        $this->data = $data;

        return $this;
    }

    public function auto(): self
    {
        $this->dir = 'auto';

        return $this;
    }

    public function ltr(): self
    {
        $this->dir = 'ltr';

        return $this;
    }

    public function rtl(): self
    {
        $this->dir = 'rtl';

        return $this;
    }

    public function withBadge(string $badge): self
    {
        $this->badge = $badge;

        return $this;
    }

    public function withIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function withImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function withLang(string $lang): self
    {
        $this->lang = $lang;

        return $this;
    }

    public function renotify(): self
    {
        $this->renotify = true;

        return $this;
    }

    public function doNotRenotify(): self
    {
        $this->renotify = false;

        return $this;
    }

    public function interactionRequired(): self
    {
        $this->requireInteraction = true;

        return $this;
    }

    public function noInteraction(): self
    {
        $this->requireInteraction = false;

        return $this;
    }

    public function mute(): self
    {
        $this->silent = true;

        return $this;
    }

    public function unmute(): self
    {
        $this->silent = false;

        return $this;
    }

    public function withTag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function withTimestamp(int $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function vibrate(int ...$vibrations): self
    {
        $this->vibrate = $vibrations;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $r = array_filter(get_object_vars($this), static function ($v): bool {
            if (is_array($v) && 0 === count($v)) {
                return false;
            }

            return null !== $v;
        });
        ksort($r);

        return $r;
    }
}
