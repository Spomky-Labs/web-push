<?php

declare(strict_types=1);

namespace WebPush;

use JetBrains\PhpStorm\Pure;
use function json_encode;
use JsonException;
use JsonSerializable;

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

    /**
     * @throws JsonException
     */
    public function toString(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    #[Pure]
    public static function create(string $action, string $title): self
    {
        return new self($action, $title);
    }

    public function withIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    #[Pure]
    public function getAction(): string
    {
        return $this->action;
    }

    #[Pure]
    public function getTitle(): string
    {
        return $this->title;
    }

    #[Pure]
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static function ($v): bool {
            return null !== $v;
        });
    }
}
