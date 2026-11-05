<?php

declare(strict_types=1);

namespace WebPush;

use JsonSerializable;
use WebPush\Exception\ValidationException;
use function count;
use function is_array;
use function json_encode;
use function trim;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * A Declarative Web Push payload.
 *
 * Such a payload is understood by the user agent itself: the notification is displayed even when no service worker is
 * registered or when it has been evicted. A service worker may still intercept the message when the payload is marked
 * as mutable.
 *
 * @see https://w3c.github.io/push-api/#declarative-push-message
 * @see https://webkit.org/blog/16535/meet-declarative-web-push/
 */
final class DeclarativeMessage implements JsonSerializable
{
    /**
     * The magic number that identifies a declarative push message. Homage to RFC8030.
     */
    public const WEB_PUSH = 8030;

    /**
     * @var array<int, DeclarativeAction>
     */
    private array $actions = [];

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

    private ?bool $mutable = null;

    private ?int $appBadge = null;

    public function __construct(
        private string $title,
        private string $navigate,
        private ?string $body = null
    ) {
        $this->setTitle($title);
        $this->setNavigate($navigate);
    }

    public static function create(string $title, string $navigate, ?string $body = null): self
    {
        return new self($title, $navigate, $body);
    }

    public function toString(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array<int, DeclarativeAction>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getNavigate(): string
    {
        return $this->navigate;
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

    public function isMutable(): ?bool
    {
        return $this->mutable;
    }

    public function getAppBadge(): ?int
    {
        return $this->appBadge;
    }

    public function addAction(DeclarativeAction $action): self
    {
        $this->actions[] = $action;

        return $this;
    }

    public function withTitle(string $title): self
    {
        $this->setTitle($title);

        return $this;
    }

    public function withNavigate(string $navigate): self
    {
        $this->setNavigate($navigate);

        return $this;
    }

    public function withBody(string $body): self
    {
        $this->body = $body;

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
     * The payload is also dispatched to the service worker, if any, allowing it to alter the notification before it is
     * displayed.
     */
    public function mutable(): self
    {
        $this->mutable = true;

        return $this;
    }

    public function immutable(): self
    {
        $this->mutable = false;

        return $this;
    }

    /**
     * Number of items displayed on the application badge. `0` clears the badge.
     *
     * This member is not part of the Push API specification yet, but it is supported by Safari.
     */
    public function withAppBadge(int $appBadge): self
    {
        if ($appBadge < 0) {
            throw new ValidationException('The application badge shall be a positive integer.');
        }
        $this->appBadge = $appBadge;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $notification = [
            'title' => $this->title,
            'navigate' => $this->navigate,
            'body' => $this->body,
            'dir' => $this->dir,
            'lang' => $this->lang,
            'tag' => $this->tag,
            'image' => $this->image,
            'icon' => $this->icon,
            'badge' => $this->badge,
            'vibrate' => $this->vibrate,
            'timestamp' => $this->timestamp,
            'renotify' => $this->renotify,
            'silent' => $this->silent,
            'requireInteraction' => $this->requireInteraction,
            'data' => $this->data,
            'actions' => $this->actions,
        ];

        $payload = [
            'web_push' => self::WEB_PUSH,
            'notification' => $this->filter($notification),
            'mutable' => $this->mutable,
            'app_badge' => $this->appBadge === null ? null : (string) $this->appBadge,
        ];

        return $this->filter($payload);
    }

    /**
     * @param array<string, mixed> $properties
     *
     * @return array<string, mixed>
     */
    private function filter(array $properties): array
    {
        return array_filter($properties, static function ($v): bool {
            if (is_array($v) && count($v) === 0) {
                return false;
            }

            return $v !== null;
        });
    }

    private function setTitle(string $title): void
    {
        if (trim($title) === '') {
            throw new ValidationException('The title shall not be empty.');
        }
        $this->title = $title;
    }

    private function setNavigate(string $navigate): void
    {
        if (trim($navigate) === '') {
            throw new ValidationException('The navigation URL shall not be empty.');
        }
        $this->navigate = $navigate;
    }
}
