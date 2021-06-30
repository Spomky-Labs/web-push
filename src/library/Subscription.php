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

use function array_key_exists;
use Assert\Assertion;
use DateTimeInterface;
use Safe\DateTimeImmutable;
use function Safe\json_decode;

class Subscription implements SubscriptionInterface
{
    private string $endpoint;

    /**
     * @var string[]
     */
    private array $keys;

    /**
     * @var string[]
     */
    private array $supportedContentEncodings = ['aesgcm'];

    private ?int $expirationTime = null;

    public function __construct(string $endpoint)
    {
        $this->endpoint = $endpoint;
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
        return null === $this->expirationTime ? null : (new DateTimeImmutable())->setTimestamp($this->expirationTime);
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
        $data = json_decode($input, true);
        Assertion::isArray($data, 'Invalid input');

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
