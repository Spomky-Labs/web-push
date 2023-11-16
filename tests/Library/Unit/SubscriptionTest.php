<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use DatetimeImmutable;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use WebPush\Exception\OperationException;
use WebPush\Subscription;
use function json_encode;
use const JSON_THROW_ON_ERROR;

/**
 * @internal
 */
final class SubscriptionTest extends TestCase
{
    #[Test]
    #[DataProvider('dataInvalidSubscription')]
    public function invalidInputCannotBeLoaded(string $input, string $exception, string $message): void
    {
        $this->expectException($exception);
        $this->expectExceptionMessage($message);

        Subscription::createFromString($input);
    }

    #[Test]
    public function createSubscriptionFluent(): void
    {
        $subscription = Subscription::create('https://foo.bar');
        $subscription
            ->setKey('p256dh', 'Public key')
            ->setKey('auth', 'Authorization Token')
        ;

        static::assertSame('https://foo.bar', $subscription->getEndpoint());
        static::assertSame('Public key', $subscription->getKey('p256dh'));
        static::assertSame('Authorization Token', $subscription->getKey('auth'));
        static::assertSame(['aesgcm'], $subscription->getSupportedContentEncodings());
    }

    #[Test]
    public function createSubscriptionFromJson(): void
    {
        $subscription = Subscription::createFromString(
            '{"endpoint": "https://some.pushservice.com/something-unique","keys": {"p256dh":"BIPUL12DLfytvTajnryr2PRdAgXS3HGKiLqndGcJGabyhHheJYlNGCeXl1dn18gSJ1WAkAPIxr4gK0_dQds4yiI=","auth":"FPssNDTKnInHVndSTdbKFw=="},"expirationTime":1580253757}'
        );

        static::assertSame('https://some.pushservice.com/something-unique', $subscription->getEndpoint());
        static::assertSame(
            'BIPUL12DLfytvTajnryr2PRdAgXS3HGKiLqndGcJGabyhHheJYlNGCeXl1dn18gSJ1WAkAPIxr4gK0_dQds4yiI=',
            $subscription->getKey('p256dh')
        );
        static::assertSame('FPssNDTKnInHVndSTdbKFw==', $subscription->getKey('auth'));
        static::assertSame(['aesgcm'], $subscription->getSupportedContentEncodings());
        static::assertSame(1_580_253_757, $subscription->getExpirationTime());
        static::assertSame(
            DatetimeImmutable::createFromFormat('Y-m-d\TH:i:sP', '2020-01-28T16:22:37-07:00')->getTimestamp(),
            $subscription->expiresAt()
                ->getTimestamp()
        );
    }

    #[Test]
    public function createSubscriptionWithoutExpirationTimeFromJson(): void
    {
        $subscription = Subscription::createFromString(
            '{"endpoint": "https://some.pushservice.com/something-unique","keys": {"p256dh":"BIPUL12DLfytvTajnryr2PRdAgXS3HGKiLqndGcJGabyhHheJYlNGCeXl1dn18gSJ1WAkAPIxr4gK0_dQds4yiI=","auth":"FPssNDTKnInHVndSTdbKFw=="}}'
        )
        ;

        static::assertSame('https://some.pushservice.com/something-unique', $subscription->getEndpoint());
        static::assertSame(
            'BIPUL12DLfytvTajnryr2PRdAgXS3HGKiLqndGcJGabyhHheJYlNGCeXl1dn18gSJ1WAkAPIxr4gK0_dQds4yiI=',
            $subscription->getKey('p256dh')
        );
        static::assertSame('FPssNDTKnInHVndSTdbKFw==', $subscription->getKey('auth'));
        static::assertSame(['aesgcm'], $subscription->getSupportedContentEncodings());
        static::assertNull($subscription->getExpirationTime());
        static::assertNull($subscription->expiresAt());
    }

    #[Test]
    public function createSubscriptionWithAESGCMENCODINGFluent(): void
    {
        $subscription = Subscription::create('https://foo.bar')
            ->withContentEncodings(['aesgcm'])
        ;

        static::assertSame('https://foo.bar', $subscription->getEndpoint());
        static::assertSame(['aesgcm'], $subscription->getSupportedContentEncodings());
    }

    #[Test]
    public function createSubscriptionWithAES128GCMENCODINGFluent(): void
    {
        $subscription = Subscription::create('https://foo.bar')
            ->withContentEncodings(['aes128gcm'])
        ;

        static::assertSame('https://foo.bar', $subscription->getEndpoint());
        static::assertSame(['aes128gcm'], $subscription->getSupportedContentEncodings());
    }

    #[Test]
    public function invalidEncodingIsDetected(): void
    {
        $this->expectException(OperationException::class);
        $this->expectExceptionMessage('Invalid input');

        Subscription::createFromString(
            '{"supportedContentEncodings": [123],"endpoint": "https://some.pushservice.com/something-unique","keys": {"p256dh":"BIPUL12DLfytvTajnryr2PRdAgXS3HGKiLqndGcJGabyhHheJYlNGCeXl1dn18gSJ1WAkAPIxr4gK0_dQds4yiI=","auth":"FPssNDTKnInHVndSTdbKFw=="}}'
        );
    }

    /**
     * @param array<string, string> $keys
     */
    #[Test]
    #[DataProvider('dataSubscription')]
    public function createSubscription(string $endpoint, string $contentEncoding, array $keys): void
    {
        $subscription = Subscription::create($endpoint)
            ->withContentEncodings([$contentEncoding])
        ;
        foreach ($keys as $k => $v) {
            $subscription->setKey($k, $v);
        }

        static::assertSame($endpoint, $subscription->getEndpoint());
        static::assertSame($keys, $subscription->getKeys());
        static::assertSame([$contentEncoding], $subscription->getSupportedContentEncodings());

        $json = json_encode($subscription, JSON_THROW_ON_ERROR);
        $newSubscription = Subscription::createFromString($json);

        static::assertSame($endpoint, $newSubscription->getEndpoint());
        static::assertSame($keys, $newSubscription->getKeys());
        static::assertSame([$contentEncoding], $newSubscription->getSupportedContentEncodings());
    }

    /**
     * @return array<int, array<string, array<string, string>|string>>
     */
    public static function dataSubscription(): iterable
    {
        yield [
            'endpoint' => 'https://foo.bar',
            'content_encoding' => 'FOO',
            'keys' => [],
        ];
        yield [
            'endpoint' => 'https://bar.foo',
            'content_encoding' => 'FOO',
            'keys' => [
                'authToken' => 'bar-foo',
                'publicKey' => 'FOO-BAR',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function dataInvalidSubscription(): iterable
    {
        yield [
            'input' => json_encode(0, JSON_THROW_ON_ERROR),
            'exception' => OperationException::class,
            'message' => 'Invalid input',
        ];
        yield [
            'input' => '',
            'exception' => JsonException::class,
            'message' => 'Syntax error',
        ];
        yield [
            'input' => '[]',
            'exception' => OperationException::class,
            'message' => 'Invalid input',
        ];
        yield [
            'input' => json_encode([
                'endpoint' => 0,
            ], JSON_THROW_ON_ERROR),
            'exception' => OperationException::class,
            'message' => 'Invalid input',
        ];
        yield [
            'input' => json_encode([
                'endpoint' => 'https://foo.bar',
                'supportedContentEncodings' => 'FOO',
                'keys' => 'foo',
            ], JSON_THROW_ON_ERROR),
            'exception' => OperationException::class,
            'message' => 'Invalid input',
        ];
        yield [
            'input' => json_encode([
                'endpoint' => 'https://foo.bar',
                'supportedContentEncodings' => [123],
                'keys' => 'foo',
            ], JSON_THROW_ON_ERROR),
            'exception' => OperationException::class,
            'message' => 'Invalid input',
        ];
        yield [
            'input' => json_encode([
                'endpoint' => 'https://foo.bar',
                'supportedContentEncodings' => ['FOO'],
                'keys' => 'foo',
            ], JSON_THROW_ON_ERROR),
            'exception' => OperationException::class,
            'message' => 'Invalid input',
        ];
        yield [
            'input' => json_encode([
                'endpoint' => 'https://foo.bar',
                'supportedContentEncodings' => ['FOO'],
                'keys' => [
                    12 => 0,
                ],
            ], JSON_THROW_ON_ERROR),
            'exception' => OperationException::class,
            'message' => 'Invalid key name',
        ];
        yield [
            'input' => json_encode([
                'endpoint' => 'https://foo.bar',
                'supportedContentEncodings' => ['FOO'],
                'keys' => [
                    'authToken' => 'BAR',
                    'publicKey' => 0,
                ],
            ], JSON_THROW_ON_ERROR),
            'exception' => OperationException::class,
            'message' => 'Invalid key value',
        ];
        yield [
            'input' => json_encode([
                'endpoint' => 'https://foo.bar',
                'supportedContentEncodings' => ['FOO'],
                'keys' => [
                    'authToken' => 'BAR',
                    'publicKey' => 0,
                ],
                'expirationTime' => 'Monday',
            ], JSON_THROW_ON_ERROR),
            'exception' => OperationException::class,
            'message' => 'Invalid input',
        ];
        yield [
            'input' => json_encode([
                'endpoint' => 'https://foo.bar',
                'supportedContentEncodings' => ['FOO'],
                'keys' => [
                    'authToken' => 'BAR',
                    'publicKey' => 'baz',
                ],
                'expirationTime' => 'Monday',
            ], JSON_THROW_ON_ERROR),
            'exception' => OperationException::class,
            'message' => 'Invalid input',
        ];
    }
}
