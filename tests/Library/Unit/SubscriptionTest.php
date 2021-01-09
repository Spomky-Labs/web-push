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

namespace WebPush\Tests\Library\Unit;

use Assert\InvalidArgumentException;
use DatetimeImmutable;
use PHPUnit\Framework\TestCase;
use Safe\Exceptions\JsonException;
use function Safe\json_encode;
use WebPush\Subscription;

/**
 * @internal
 * @group Unit
 * @group Library
 */
final class SubscriptionTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataInvalidSubscription
     * @psalm-param class-string<\Throwable> $exception
     */
    public function invalidInputCannotBeLoaded(string $input, string $exception, string $message): void
    {
        $this->expectException($exception);
        $this->expectExceptionMessage($message);

        Subscription::createFromString($input);
    }

    /**
     * @test
     */
    public function createSubscriptionFluent(): void
    {
        $subscription = Subscription::create('https://foo.bar');
        $subscription->getKeys()
            ->set('p256dh', 'Public key')
            ->set('auth', 'Authorization Token')
        ;

        static::assertEquals('https://foo.bar', $subscription->getEndpoint());
        static::assertEquals('Public key', $subscription->getKeys()->get('p256dh'));
        static::assertEquals('Authorization Token', $subscription->getKeys()->get('auth'));
        static::assertEquals(['aesgcm'], $subscription->getSupportedContentEncodings());
    }

    /**
     * @test
     */
    public function createSubscriptionFromJson(): void
    {
        $subscription = Subscription::createFromString('{"endpoint": "https://some.pushservice.com/something-unique","keys": {"p256dh":"BIPUL12DLfytvTajnryr2PRdAgXS3HGKiLqndGcJGabyhHheJYlNGCeXl1dn18gSJ1WAkAPIxr4gK0_dQds4yiI=","auth":"FPssNDTKnInHVndSTdbKFw=="},"expirationTime":1580253757}');

        static::assertEquals('https://some.pushservice.com/something-unique', $subscription->getEndpoint());
        static::assertEquals('BIPUL12DLfytvTajnryr2PRdAgXS3HGKiLqndGcJGabyhHheJYlNGCeXl1dn18gSJ1WAkAPIxr4gK0_dQds4yiI=', $subscription->getKeys()->get('p256dh'));
        static::assertEquals('FPssNDTKnInHVndSTdbKFw==', $subscription->getKeys()->get('auth'));
        static::assertEquals(['aesgcm'], $subscription->getSupportedContentEncodings());
        static::assertEquals(1580253757, $subscription->getExpirationTime());
        static::assertEquals(DatetimeImmutable::createFromFormat('Y-m-d\TH:i:sP', '2020-01-28T16:22:37-07:00'), $subscription->expiresAt());
    }

    /**
     * @test
     */
    public function createSubscriptionWithoutExpirationTimeFromJson(): void
    {
        $subscription = Subscription::createFromString('{"endpoint": "https://some.pushservice.com/something-unique","keys": {"p256dh":"BIPUL12DLfytvTajnryr2PRdAgXS3HGKiLqndGcJGabyhHheJYlNGCeXl1dn18gSJ1WAkAPIxr4gK0_dQds4yiI=","auth":"FPssNDTKnInHVndSTdbKFw=="}}');

        static::assertEquals('https://some.pushservice.com/something-unique', $subscription->getEndpoint());
        static::assertEquals('BIPUL12DLfytvTajnryr2PRdAgXS3HGKiLqndGcJGabyhHheJYlNGCeXl1dn18gSJ1WAkAPIxr4gK0_dQds4yiI=', $subscription->getKeys()->get('p256dh'));
        static::assertEquals('FPssNDTKnInHVndSTdbKFw==', $subscription->getKeys()->get('auth'));
        static::assertEquals(['aesgcm'], $subscription->getSupportedContentEncodings());
        static::assertNull($subscription->getExpirationTime());
        static::assertNull($subscription->expiresAt());
    }

    /**
     * @test
     */
    public function invalidExpirationTime(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Invalid input');

        Subscription::createFromString('{"endpoint": "https://some.pushservice.com/something-unique","keys": {"p256dh":"BIPUL12DLfytvTajnryr2PRdAgXS3HGKiLqndGcJGabyhHheJYlNGCeXl1dn18gSJ1WAkAPIxr4gK0_dQds4yiI=","auth":"FPssNDTKnInHVndSTdbKFw=="},"expirationTime":"Hello World"}');
    }

    /**
     * @test
     */
    public function createSubscriptionWithAESGCMENCODINGFluent(): void
    {
        $subscription = Subscription::create('https://foo.bar')
            ->withContentEncodings(['aesgcm'])
        ;

        static::assertEquals('https://foo.bar', $subscription->getEndpoint());
        static::assertEquals(['aesgcm'], $subscription->getSupportedContentEncodings());
    }

    /**
     * @test
     */
    public function createSubscriptionWithAES128GCMENCODINGFluent(): void
    {
        $subscription = Subscription::create('https://foo.bar')
            ->withContentEncodings(['aes128gcm'])
        ;

        static::assertEquals('https://foo.bar', $subscription->getEndpoint());
        static::assertEquals(['aes128gcm'], $subscription->getSupportedContentEncodings());
    }

    /**
     * @test
     * @dataProvider dataSubscription
     *
     * @param array<string, string> $keys
     */
    public function createSubscription(string $endpoint, string $contentEncoding, array $keys): void
    {
        $subscription = Subscription::create($endpoint)
            ->withContentEncodings([$contentEncoding])
        ;
        foreach ($keys as $k => $v) {
            $subscription->getKeys()->set($k, $v);
        }

        static::assertEquals($endpoint, $subscription->getEndpoint());
        static::assertEquals($keys, $subscription->getKeys()->all());
        static::assertEquals([$contentEncoding], $subscription->getSupportedContentEncodings());

        $json = json_encode($subscription);
        $newSubscription = Subscription::createFromString($json);

        static::assertEquals($endpoint, $newSubscription->getEndpoint());
        static::assertEquals($keys, $newSubscription->getKeys()->all());
        static::assertEquals([$contentEncoding], $newSubscription->getSupportedContentEncodings());
    }

    /**
     * @return array<int, array<string, array<string, string>|string>>
     */
    public function dataSubscription(): array
    {
        return [
            [
                'endpoint' => 'https://foo.bar',
                'content_encoding' => 'FOO',
                'keys' => [],
            ],
            [
                'endpoint' => 'https://bar.foo',
                'content_encoding' => 'FOO',
                'keys' => [
                    'authToken' => 'bar-foo',
                    'publicKey' => 'FOO-BAR',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function dataInvalidSubscription(): array
    {
        return [
            [
                'input' => json_encode(0),
                'exception' => InvalidArgumentException::class,
                'message' => 'Invalid input',
            ],
            [
                'input' => '',
                'exception' => JsonException::class,
                'message' => 'Syntax error',
            ],
            [
                'input' => '[]',
                'exception' => InvalidArgumentException::class,
                'message' => 'Invalid input',
            ],
            [
                'input' => json_encode([
                    'endpoint' => 0,
                ]),
                'exception' => InvalidArgumentException::class,
                'message' => 'Invalid input',
            ],
            [
                'input' => json_encode([
                    'endpoint' => 'https://foo.bar',
                    'contentEncoding' => 'FOO',
                    'keys' => 'foo',
                ]),
                'exception' => InvalidArgumentException::class,
                'message' => 'Invalid input',
            ],
            [
                'input' => json_encode([
                    'endpoint' => 'https://foo.bar',
                    'contentEncoding' => 'FOO',
                    'keys' => [
                        12 => 0,
                    ],
                ]),
                'exception' => InvalidArgumentException::class,
                'message' => 'Invalid key name',
            ],
            [
                'input' => json_encode([
                    'endpoint' => 'https://foo.bar',
                    'contentEncoding' => 'FOO',
                    'keys' => [
                        'authToken' => 'BAR',
                        'publicKey' => 0,
                    ],
                ]),
                'exception' => InvalidArgumentException::class,
                'message' => 'Invalid key value',
            ],
            [
                'input' => json_encode([
                    'endpoint' => 'https://foo.bar',
                    'contentEncoding' => 'FOO',
                    'keys' => [
                        'authToken' => 'BAR',
                        'publicKey' => 0,
                    ],
                    'expirationTime' => 'Monday',
                ]),
                'exception' => InvalidArgumentException::class,
                'message' => 'Invalid input',
            ],
        ];
    }
}
