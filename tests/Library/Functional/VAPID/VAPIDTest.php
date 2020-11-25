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

namespace WebPush\Tests\Library\Functional\VAPID;

use function array_key_exists;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use WebPush\Notification;
use WebPush\Subscription;
use WebPush\VAPID\Header;
use WebPush\VAPID\JWSProvider;
use WebPush\VAPID\VAPIDExtension;

/**
 * @internal
 * @group Functional
 * @group Library
 */
final class VAPIDTest extends TestCase
{
    /**
     * @test
     */
    public function vapidHeaderCanBeAdded(): void
    {
        $jwsProvider = self::createMock(JWSProvider::class);
        $jwsProvider
            ->expects(static::once())
            ->method('computeHeader')
            ->with()
            ->willReturnCallback(static function (array $parameters): Header {
                static::assertArrayHasKey('aud', $parameters);
                static::assertArrayHasKey('sub', $parameters);
                static::assertArrayHasKey('exp', $parameters);
                static::assertEquals('https://foo.fr', $parameters['aud']);
                static::assertEquals('subject', $parameters['sub']);
                static::assertIsInt($parameters['exp']);

                return new Header('TOKEN', 'KEY');
            })
        ;

        $logger = self::createMock(LoggerInterface::class);
        $logger
            ->expects(static::exactly(3))
            ->method('debug')
            ->withConsecutive(
                ['Processing with VAPID header'],
                ['Caching feature is not available'],
                ['Generated header', static::callback(static function (array $data): bool {
                    if (!array_key_exists('header', $data)) {
                        return false;
                    }

                    return $data['header'] instanceof Header;
                })],
            )
        ;

        $request = self::createMock(RequestInterface::class);
        $request
            ->expects(static::once())
            ->method('withAddedHeader')
            ->with('Authorization', 'vapid t=TOKEN, k=KEY')
            ->willReturnSelf()
        ;

        $notification = self::createMock(Notification::class);
        $subscription = self::createMock(Subscription::class);
        $subscription
            ->expects(static::once())
            ->method('getEndpoint')
            ->willReturn('https://foo.fr/test')
        ;

        VAPIDExtension::create('subject', $jwsProvider)
            ->setLogger($logger)
            ->process($request, $notification, $subscription)
        ;
    }

    /**
     * @test
     */
    public function vapidWithCacheHeaderCanBeAdded(): void
    {
        $logger = self::createMock(LoggerInterface::class);
        $logger
            ->expects(static::exactly(3))
            ->method('debug')
            ->withConsecutive(
                ['Processing with VAPID header'],
                ['Caching feature is available'],
                ['Header from cache', static::callback(static function (array $data): bool {
                    if (!array_key_exists('header', $data)) {
                        return false;
                    }

                    return $data['header'] instanceof Header;
                })],
            )
        ;

        $cacheItem = self::createMock(CacheItemInterface::class);
        $cacheItem
            ->expects(static::once())
            ->method('isHit')
            ->willReturn(true)
        ;
        $cacheItem
            ->expects(static::once())
            ->method('get')
            ->willReturn(new Header('TOKEN__CACHE', 'KEY__CACHE'))
        ;
        $cache = self::createMock(CacheItemPoolInterface::class);
        $cache
            ->expects(static::once())
            ->method('getItem')
            ->with(hash('sha512', 'https://foo.fr'))
            ->willReturn($cacheItem)
        ;
        $cache
            ->expects(static::never())
            ->method('save')
        ;

        $jwsProvider = self::createMock(JWSProvider::class);
        $jwsProvider
            ->expects(static::never())
            ->method(static::anything())
        ;

        $request = self::createMock(RequestInterface::class);
        $request
            ->expects(static::once())
            ->method('withAddedHeader')
            ->withConsecutive(
                ['Authorization', 'vapid t=TOKEN__CACHE, k=KEY__CACHE'],
                //['Crypto-Key', static::isType('string')],
            )
            ->willReturnSelf()
        ;

        $notification = self::createMock(Notification::class);
        $subscription = self::createMock(Subscription::class);
        $subscription
            ->expects(static::once())
            ->method('getEndpoint')
            ->willReturn('https://foo.fr/test')
        ;

        VAPIDExtension::create('subject', $jwsProvider)
            ->setLogger($logger)
            ->setCache($cache, 'now +30min')
            ->setTokenExpirationTime('now +2 hours')
            ->process($request, $notification, $subscription)
        ;
    }

    /**
     * @test
     */
    public function vapidWithMissingCacheHeaderCanBeGenerated(): void
    {
        $cacheItem = self::createMock(CacheItemInterface::class);
        $cacheItem
            ->expects(static::once())
            ->method('isHit')
            ->willReturn(false)
        ;
        $cacheItem
            ->expects(static::once())
            ->method('set')
            ->with(static::isInstanceOf(Header::class))
            ->willReturnSelf()
        ;
        $cacheItem
            ->expects(static::once())
            ->method('expiresAt')
            ->with(static::isInstanceOf(DateTimeInterface::class))
            ->willReturnSelf()
        ;
        $cache = self::createMock(CacheItemPoolInterface::class);
        $cache
            ->expects(static::once())
            ->method('getItem')
            ->with(hash('sha512', 'https://foo.fr:8080'))
            ->willReturn($cacheItem)
        ;
        $cache
            ->expects(static::once())
            ->method('save')
        ;

        $jwsProvider = self::createMock(JWSProvider::class);
        $jwsProvider
            ->expects(static::once())
            ->method('computeHeader')
        ;

        $request = self::createMock(RequestInterface::class);
        $request
            ->expects(static::once())
            ->method('withAddedHeader')
            ->with('Authorization', static::isType('string'))
            ->willReturnSelf()
        ;

        $notification = self::createMock(Notification::class);
        $subscription = self::createMock(Subscription::class);
        $subscription
            ->expects(static::once())
            ->method('getEndpoint')
            ->willReturn('https://foo.fr:8080/test')
        ;

        VAPIDExtension::create('subject', $jwsProvider)
            ->setTokenExpirationTime('now +2 hours')
            ->setCache($cache, 'now +30min')
            ->process($request, $notification, $subscription)
        ;
    }
}
