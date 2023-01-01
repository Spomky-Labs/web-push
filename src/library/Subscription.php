<?php

declare(strict_types=1);

namespace WebPush;

use function array_key_exists;
use Assert\Assertion;
use DateTimeImmutable;
use DateTimeInterface;
use JetBrains\PhpStorm\ArrayShape;
use function json_decode;
use const JSON_THROW_ON_ERROR;

class Subscription implements SubscriptionInterface
{
    /**
     * @var string[]
     */
    private array $keys;

    /**
     * @var string[]
     */
    private array $supportedContentEncodings = ['aesgcm'];

    private ?int $expirationTime = null;

    public function __construct(
        private readonly string $endpoint
    ) {
        $this->keys = [];
    }

    public static function create(string $endpoint): self
    {
        return new self($endpoint);
    }

    /**
     * @param string[] $contentEncodings
     */
    public function withContentEncodings(array $contentEncodings): self
    {
        $this->supportedContentEncodings = $contentEncodings;

        return $this;
    }

    public function getKeys(): array
    {
        return $this->keys;
    }

    public function hasKey(string $key): bool
    {
        return isset($this->keys[$key]);
    }

    public function getKey(string $key): string
    {
        Assertion::keyExists($this->keys, $key, 'The key does not exist');

        return $this->keys[$key];
    }

    public function setKey(string $key, string $value): self
    {
        $this->keys[$key] = $value;

        return $this;
    }

    public function getExpirationTime(): ?int
    {
        return $this->expirationTime;
    }

    public function setExpirationTime(?int $expirationTime): self
    {
        $this->expirationTime = $expirationTime;

        return $this;
    }

    public function expiresAt(): ?DateTimeInterface
    {
        return $this->expirationTime === null ? null : (new DateTimeImmutable())->setTimestamp($this->expirationTime);
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @return string[]
     */
    public function getSupportedContentEncodings(): array
    {
        return $this->supportedContentEncodings;
    }

    public static function createFromString(string $input): self
    {
        $data = json_decode($input, true, 512, JSON_THROW_ON_ERROR);

        Assertion::isArray($data, 'Invalid input');

        return self::createFromAssociativeArray($data);
    }

    /**
     * @return array<string, string|string[]>
     */
    #[ArrayShape([
        'endpoint' => 'string',
        'supportedContentEncodings' => 'string[]',
        'keys' => 'string[]',
    ])]
    public function jsonSerialize(): array
    {
        return [
            'endpoint' => $this->endpoint,
            'supportedContentEncodings' => $this->supportedContentEncodings,
            'keys' => $this->keys,
        ];
    }

    /**
     * @param array<string, mixed> $input
     */
    private static function createFromAssociativeArray(array $input): self
    {
        Assertion::keyExists($input, 'endpoint', 'Invalid input');
        Assertion::string($input['endpoint'], 'Invalid input');

        $object = new self($input['endpoint']);
        if (array_key_exists('supportedContentEncodings', $input)) {
            $encodings = $input['supportedContentEncodings'];
            Assertion::isArray($encodings, 'Invalid input');
            Assertion::allString($encodings, 'Invalid input');
            $object->withContentEncodings($encodings);
        }
        if (array_key_exists('expirationTime', $input)) {
            Assertion::nullOrInteger($input['expirationTime'], 'Invalid input');
            $object->setExpirationTime($input['expirationTime']);
        }
        if (array_key_exists('keys', $input)) {
            Assertion::isArray($input['keys'], 'Invalid input');
            foreach ($input['keys'] as $k => $v) {
                Assertion::string($k, 'Invalid key name');
                Assertion::string($v, 'Invalid key value');
                $object->setKey($k, $v);
            }
        }

        return $object;
    }
}
