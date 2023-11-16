<?php

declare(strict_types=1);

namespace WebPush;

use DateTimeImmutable;
use DateTimeInterface;
use WebPush\Exception\OperationException;
use function array_key_exists;
use function is_array;
use function is_int;
use function is_string;
use function json_decode;
use const ARRAY_FILTER_USE_KEY;
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
        array_key_exists($key, $this->keys) || throw new OperationException('The key does not exist');

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

        is_array($data) || throw new OperationException('Invalid input');
        array_walk($data, static function (mixed $item, string|int $key): void {
            is_string($key) || throw new OperationException('Invalid input');
        }, ARRAY_FILTER_USE_KEY);

        return self::createFromAssociativeArray($data);
    }

    /**
     * @return array<string, string|string[]>
     */
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
        array_key_exists('endpoint', $input) || throw new OperationException('Invalid input');
        is_string($input['endpoint']) || throw new OperationException('Invalid input');

        $object = new self($input['endpoint']);
        if (array_key_exists('supportedContentEncodings', $input)) {
            $encodings = $input['supportedContentEncodings'];
            is_array($encodings) || throw new OperationException('Invalid input');
            array_walk($encodings, static function (mixed $item): void {
                is_string($item) || throw new OperationException('Invalid input');
            });
            $object->withContentEncodings($encodings);
        }
        if (array_key_exists('expirationTime', $input)) {
            $input['expirationTime'] === null || is_int($input['expirationTime']) || throw new OperationException(
                'Invalid input'
            );
            $object->setExpirationTime($input['expirationTime']);
        }
        if (array_key_exists('keys', $input)) {
            is_array($input['keys']) || throw new OperationException('Invalid input');
            foreach ($input['keys'] as $k => $v) {
                is_string($k) || throw new OperationException('Invalid key name');
                is_string($v) || throw new OperationException('Invalid key value');
                $object->setKey($k, $v);
            }
        }

        return $object;
    }
}
