<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2020 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace WebPush\Tests\Library\Unit;

use function array_key_exists;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use WebPush\ExtensionManager;
use WebPush\Notification;
use WebPush\StatusReport;
use WebPush\Subscription;
use WebPush\WebPush;

/**
 * @internal
 * @group Unit
 * @group Library
 */
class WebPushTest extends TestCase
{
    /**
     * @test
     */
    public function aNotificationCanBeSent(): void
    {
        $response = self::createMock(ResponseInterface::class);
        $response
            ->expects(static::once())
            ->method('getStatusCode')
            ->willReturn(201)
        ;

        $request = self::createMock(RequestInterface::class);
        $request
            ->expects(static::never())
            ->method(static::anything())
        ;

        $subscription = self::createMock(Subscription::class);
        $subscription
            ->expects(static::once())
            ->method('getEndpoint')
            ->willReturn('https://foo.bar')
        ;

        $notification = self::createMock(Notification::class);
        $notification
            ->expects(static::never())
            ->method(static::anything())
        ;

        $client = self::createMock(ClientInterface::class);
        $client
            ->expects(static::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response)
        ;

        $requestFactory = self::createMock(RequestFactoryInterface::class);
        $requestFactory
            ->expects(static::once())
            ->method('createRequest')
            ->with('POST', 'https://foo.bar')
            ->willReturn($request)
        ;

        $extensionManager = self::createMock(ExtensionManager::class);
        $extensionManager
            ->expects(static::once())
            ->method('process')
            ->with($request, $notification, $subscription)
            ->willReturnArgument(0)
        ;

        $logger = self::createMock(LoggerInterface::class);
        $logger
            ->expects(static::exactly(3))
            ->method('debug')
            ->withConsecutive(
                ['Sending notification', static::callback(static function (array $data) use ($notification): bool {
                    if (!array_key_exists('notification', $data)) {
                        return false;
                    }

                    return $data['notification'] === $notification;
                })],
                ['Request ready', static::callback(static function (array $data) use ($request): bool {
                    if (!array_key_exists('request', $data)) {
                        return false;
                    }

                    return $data['request'] === $request;
                })],
                ['Response received', static::callback(static function (array $data) use ($response): bool {
                    if (!array_key_exists('response', $data)) {
                        return false;
                    }

                    return $data['response'] === $response;
                })],
            )
        ;

        $eventDispatcher = self::createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->with(static::isInstanceOf(StatusReport::class))
        ;

        $webPush = WebPush::create($client, $requestFactory, $extensionManager);
        $report = $webPush
            ->setLogger($logger)
            ->setEventDispatcher($eventDispatcher)
            ->send($notification, $subscription)
        ;

        static::assertTrue($report->isSuccess());
        static::assertSame($notification, $report->getNotification());
        static::assertSame($subscription, $report->getSubscription());
    }

    /**
     * @test
     */
    public function aNotificationCanBeSentAsync(): void
    {
        $response = self::createMock(ResponseInterface::class);
        $response
            ->expects(static::once())
            ->method('getStatusCode')
            ->willReturn(202)
        ;

        $request = self::createMock(RequestInterface::class);
        $request
            ->expects(static::never())
            ->method(static::anything())
        ;

        $subscription = self::createMock(Subscription::class);
        $subscription
            ->expects(static::once())
            ->method('getEndpoint')
            ->willReturn('https://foo.bar')
        ;

        $notification = self::createMock(Notification::class);
        $notification
            ->expects(static::never())
            ->method(static::anything())
        ;

        $client = self::createMock(ClientInterface::class);
        $client
            ->expects(static::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response)
        ;

        $requestFactory = self::createMock(RequestFactoryInterface::class);
        $requestFactory
            ->expects(static::once())
            ->method('createRequest')
            ->with('POST', 'https://foo.bar')
            ->willReturn($request)
        ;

        $extensionManager = self::createMock(ExtensionManager::class);
        $extensionManager
            ->expects(static::once())
            ->method('process')
            ->with($request, $notification, $subscription)
            ->willReturnArgument(0)
        ;

        $logger = self::createMock(LoggerInterface::class);
        $logger
            ->expects(static::exactly(3))
            ->method('debug')
            ->withConsecutive(
                ['Sending notification', static::callback(static function (array $data) use ($notification): bool {
                    if (!array_key_exists('notification', $data)) {
                        return false;
                    }

                    return $data['notification'] === $notification;
                })],
                ['Request ready', static::callback(static function (array $data) use ($request): bool {
                    if (!array_key_exists('request', $data)) {
                        return false;
                    }

                    return $data['request'] === $request;
                })],
                ['Response received', static::callback(static function (array $data) use ($response): bool {
                    if (!array_key_exists('response', $data)) {
                        return false;
                    }

                    return $data['response'] === $response;
                })],
            )
        ;

        $eventDispatcher = self::createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->with(static::isInstanceOf(StatusReport::class))
        ;

        $webPush = WebPush::create($client, $requestFactory, $extensionManager);
        $report = $webPush
            ->setLogger($logger)
            ->setEventDispatcher($eventDispatcher)
            ->send($notification, $subscription)
        ;

        static::assertTrue($report->isSuccess());
        static::assertSame($notification, $report->getNotification());
        static::assertSame($subscription, $report->getSubscription());
    }

    /**
     * @test
     */
    public function aNotificationCannotBeSent(): void
    {
        $response = self::createMock(ResponseInterface::class);
        $response
            ->expects(static::once())
            ->method('getStatusCode')
            ->willReturn(409)
        ;

        $request = self::createMock(RequestInterface::class);
        $request
            ->expects(static::never())
            ->method(static::anything())
        ;

        $subscription = self::createMock(Subscription::class);
        $subscription
            ->expects(static::once())
            ->method('getEndpoint')
            ->willReturn('https://foo.bar')
        ;

        $notification = self::createMock(Notification::class);
        $notification
            ->expects(static::never())
            ->method(static::anything())
        ;

        $client = self::createMock(ClientInterface::class);
        $client
            ->expects(static::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response)
        ;

        $requestFactory = self::createMock(RequestFactoryInterface::class);
        $requestFactory
            ->expects(static::once())
            ->method('createRequest')
            ->with('POST', 'https://foo.bar')
            ->willReturn($request)
        ;

        $extensionManager = self::createMock(ExtensionManager::class);
        $extensionManager
            ->expects(static::once())
            ->method('process')
            ->with($request, $notification, $subscription)
            ->willReturnArgument(0)
        ;

        $logger = self::createMock(LoggerInterface::class);
        $logger
            ->expects(static::exactly(3))
            ->method('debug')
            ->withConsecutive(
                ['Sending notification', static::callback(static function (array $data) use ($notification): bool {
                    if (!array_key_exists('notification', $data)) {
                        return false;
                    }

                    return $data['notification'] === $notification;
                })],
                ['Request ready', static::callback(static function (array $data) use ($request): bool {
                    if (!array_key_exists('request', $data)) {
                        return false;
                    }

                    return $data['request'] === $request;
                })],
                ['Response received', static::callback(static function (array $data) use ($response): bool {
                    if (!array_key_exists('response', $data)) {
                        return false;
                    }

                    return $data['response'] === $response;
                })],
            )
        ;

        $eventDispatcher = self::createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->with(static::isInstanceOf(StatusReport::class))
        ;

        $webPush = WebPush::create($client, $requestFactory, $extensionManager);
        $report = $webPush
            ->setLogger($logger)
            ->setEventDispatcher($eventDispatcher)
            ->send($notification, $subscription)
        ;

        static::assertFalse($report->isSuccess());
        static::assertSame($notification, $report->getNotification());
        static::assertSame($subscription, $report->getSubscription());
    }
}
