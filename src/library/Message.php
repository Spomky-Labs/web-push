<?php

declare(strict_types=1);

namespace WebPush;

use function count;
use function is_array;
use function json_encode;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use JsonSerializable;

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

    /**
     * @var mixed|null
     */
    private mixed $data = null;

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

    public function __construct(
        private string $title,
        private ?string $body = null
    ) {
    }

    public function toString(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function create(string $title, ?string $body = null): self
    {
        return new self($title, $body);
    }

    /**
     * @return array<int, Action>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getData(): mixed
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

    public function getTitle(): ?string
    {
        return $this->title;
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

    public function withData(mixed $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function auto(): self
    {
        $this->dir = 'auto';

        return $this;
    }

    public function withTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function withBody(string $body): self
    {
        $this->body = $body;

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
        $this->vibrate = array_values($vibrations);

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $properties = get_object_vars($this);
        unset($properties['title']);

        return [
            'title' => $this->title,
            'options' => $this->getOptions($properties),
        ];
    }

    /**
     * @param array<string, mixed> $properties
     *
     * @return array<string, mixed>
     */
    private function getOptions(array $properties): array
    {
        return array_filter($properties, static function ($v): bool {
            if (is_array($v) && count($v) === 0) {
                return false;
            }

            return $v !== null;
        });
    }
}
