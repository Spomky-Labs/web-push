<?php

declare(strict_types=1);

namespace WebPush;

use function array_key_exists;
use Assert\Assertion;

class Notification implements NotificationInterface
{
    private ?string $payload = null;

    private int $ttl = 0;

    private string $urgency = self::URGENCY_NORMAL;

    private ?string $topic = null;

    private bool $respondAsync = false;

    /**
     * @var array<string, mixed>
     */
    private array $metadata = [];

    public static function create(): self
    {
        return new self();
    }

    public function veryLowUrgency(): self
    {
        $this->urgency = self::URGENCY_VERY_LOW;

        return $this;
    }

    public function lowUrgency(): self
    {
        $this->urgency = self::URGENCY_LOW;

        return $this;
    }

    public function normalUrgency(): self
    {
        $this->urgency = self::URGENCY_NORMAL;

        return $this;
    }

    public function highUrgency(): self
    {
        $this->urgency = self::URGENCY_HIGH;

        return $this;
    }

    public function withUrgency(string $urgency): self
    {
        Assertion::inArray($urgency, [
            self::URGENCY_VERY_LOW,
            self::URGENCY_LOW,
            self::URGENCY_NORMAL,
            self::URGENCY_HIGH,
        ], 'Invalid urgency parameter');
        $this->urgency = $urgency;

        return $this;
    }

    public function getUrgency(): string
    {
        return $this->urgency;
    }

    public function withPayload(string $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public function getPayload(): ?string
    {
        return $this->payload;
    }

    public function withTopic(string $topic): self
    {
        Assertion::notBlank($topic, 'Invalid topic');
        $this->topic = $topic;

        return $this;
    }

    public function getTopic(): ?string
    {
        return $this->topic;
    }

    public function withTTL(int $ttl): self
    {
        Assertion::greaterOrEqualThan($ttl, 0, 'Invalid TTL');
        $this->ttl = $ttl;

        return $this;
    }

    public function getTTL(): int
    {
        return $this->ttl;
    }

    public function sync(): self
    {
        $this->respondAsync = false;

        return $this;
    }

    public function async(): self
    {
        $this->respondAsync = true;

        return $this;
    }

    public function isAsync(): bool
    {
        return $this->respondAsync;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function add(string $key, mixed $data): self
    {
        $this->metadata[$key] = $data;

        return $this;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->metadata);
    }

    public function get(string $key): mixed
    {
        Assertion::true($this->has($key), 'Missing metadata');

        return $this->metadata[$key];
    }
}
