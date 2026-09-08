<?php

declare(strict_types=1);

namespace WebPush;

use function json_encode;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use JsonSerializable;

/**
 * @see https://notifications.spec.whatwg.org/#actions
 */
final class Action implements JsonSerializable
{
    private ?string $icon = null;

    public function __construct(
        private readonly string $action,
        private readonly string $title
    ) {
    }

    public function toString(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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
        /** @var array<string, mixed> $properties */
        $properties = get_object_vars($this);

        return array_filter($properties, static fn ($v): bool => $v !== null);
    }
}
