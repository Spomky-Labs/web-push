<?php

declare(strict_types=1);

namespace WebPush;

use JsonSerializable;
use WebPush\Exception\ValidationException;
use function json_encode;
use function trim;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * An action of a Declarative Web Push message.
 *
 * Unlike WebPush\Action, the "navigate" member is required: the user agent needs to know where to go when the action
 * is activated, as no service worker may be involved.
 *
 * @see https://w3c.github.io/push-api/#declarative-push-message
 * @see https://webkit.org/blog/16535/meet-declarative-web-push/
 */
final class DeclarativeAction implements JsonSerializable
{
    private ?string $icon = null;

    public function __construct(
        private readonly string $action,
        private readonly string $title,
        private readonly string $navigate
    ) {
        if (trim($action) === '') {
            throw new ValidationException('The action shall not be empty.');
        }
        if (trim($title) === '') {
            throw new ValidationException('The title shall not be empty.');
        }
        if (trim($navigate) === '') {
            throw new ValidationException('The navigation URL shall not be empty.');
        }
    }

    public function toString(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function create(string $action, string $title, string $navigate): self
    {
        return new self($action, $title, $navigate);
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

    public function getNavigate(): string
    {
        return $this->navigate;
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
        $data = [
            'action' => $this->action,
            'title' => $this->title,
            'navigate' => $this->navigate,
            'icon' => $this->icon,
        ];

        return array_filter($data, static fn ($v): bool => $v !== null);
    }
}
