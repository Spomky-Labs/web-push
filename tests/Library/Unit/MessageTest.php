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

use PHPUnit\Framework\TestCase;
use function Safe\json_encode;
use WebPush\Action;
use WebPush\Message;

/**
 * @internal
 * @group Unit
 * @group Library
 */
final class MessageTest extends TestCase
{
    /**
     * @test
     */
    public function createSimpleMessage(): void
    {
        $message = Message::create('BODY')
        ;

        static::assertEquals('BODY', $message->getBody());
        static::assertNull($message->getTimestamp());
        static::assertNull($message->getTag());
        static::assertNull($message->getData());
        static::assertNull($message->getBadge());
        static::assertNull($message->getIcon());
        static::assertNull($message->getImage());
        static::assertNull($message->getLang());
        static::assertEquals([], $message->getActions());
        static::assertNull($message->getVibrate());
        static::assertNull($message->getDir());
        static::assertNull($message->isSilent());
        static::assertNull($message->getRenotify());
        static::assertNull($message->isInteractionRequired());

        $expectedJson = '{"body":"BODY"}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createMessageWithOptions(): void
    {
        $action = Action::create('A', 'T');
        $message = Message::create('BODY')
            ->withTag('TAG')
            ->withTimestamp(1604141464)
            ->withLang('en-GB')
            ->withImage('https://image.svg')
            ->withBadge('BADGE')
            ->withIcon('https://icon.ico')
            ->withData(['foo' => 'BAR', 1, 2, 3])
            ->addAction($action)
            ->vibrate(300, 10, 200, 10, 500)
        ;

        static::assertEquals('BODY', $message->getBody());
        static::assertEquals(1604141464, $message->getTimestamp());
        static::assertEquals('TAG', $message->getTag());
        static::assertEquals(['foo' => 'BAR', 1, 2, 3], $message->getData());
        static::assertEquals('BADGE', $message->getBadge());
        static::assertEquals('https://icon.ico', $message->getIcon());
        static::assertEquals('https://image.svg', $message->getImage());
        static::assertEquals('en-GB', $message->getLang());
        static::assertEquals([$action], $message->getActions());
        static::assertEquals([300, 10, 200, 10, 500], $message->getVibrate());
        static::assertNull($message->getDir());
        static::assertNull($message->isSilent());
        static::assertNull($message->getRenotify());
        static::assertNull($message->isInteractionRequired());

        $expectedJson = '{"actions":[{"action":"A","title":"T"}],"badge":"BADGE","body":"BODY","data":{"foo":"BAR","0":1,"1":2,"2":3},"icon":"https://icon.ico","image":"https://image.svg","lang":"en-GB","tag":"TAG","timestamp":1604141464,"vibrate":[300,10,200,10,500]}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createMessageWithAutoDirection(): void
    {
        $message = Message::create('BODY')
            ->auto()
        ;
        static::assertEquals('auto', $message->getDir());

        $expectedJson = '{"body":"BODY","dir":"auto"}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createMessageWithLTRDirection(): void
    {
        $message = Message::create('BODY')
            ->ltr()
        ;
        static::assertEquals('ltr', $message->getDir());

        $expectedJson = '{"body":"BODY","dir":"ltr"}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createMessageWithRTLDirection(): void
    {
        $message = Message::create('BODY')
            ->rtl()
        ;
        static::assertEquals('rtl', $message->getDir());

        $expectedJson = '{"body":"BODY","dir":"rtl"}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createMessageWithInteraction(): void
    {
        $message = Message::create('BODY')
            ->interactionRequired()
        ;
        static::assertTrue($message->isInteractionRequired());

        $expectedJson = '{"body":"BODY","requireInteraction":true}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createMessageWithoutInteraction(): void
    {
        $message = Message::create('BODY')
            ->noInteraction()
        ;
        static::assertFalse($message->isInteractionRequired());

        $expectedJson = '{"body":"BODY","requireInteraction":false}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createSilentMessage(): void
    {
        $message = Message::create('BODY')
            ->mute()
        ;
        static::assertTrue($message->isSilent());

        $expectedJson = '{"body":"BODY","silent":true}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createNonSilentMessage(): void
    {
        $message = Message::create('BODY')
            ->unmute()
        ;
        static::assertFalse($message->isSilent());

        $expectedJson = '{"body":"BODY","silent":false}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createWithRenotification(): void
    {
        $message = Message::create('BODY')
            ->renotify()
        ;
        static::assertTrue($message->getRenotify());

        $expectedJson = '{"body":"BODY","renotify":true}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createWithoutRenotification(): void
    {
        $message = Message::create('BODY')
            ->doNotRenotify()
        ;
        static::assertFalse($message->getRenotify());

        $expectedJson = '{"body":"BODY","renotify":false}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
