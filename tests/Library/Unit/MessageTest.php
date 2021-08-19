<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use function json_encode;
use PHPUnit\Framework\TestCase;
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
        $message = Message::create('TITLE');

        static::assertNull($message->getBody());
        static::assertEquals('TITLE', $message->getTitle());
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

        $expectedJson = '{"title":"TITLE","options":[]}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createMessageWithOptionsAndOldStructure(): void
    {
        $action = Action::create('A', 'T');
        $message = Message::create('TITLE', 'BODY')
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

        $expectedJson = '{"title":"TITLE","options":{"actions":[{"action":"A","title":"T"}],"body":"BODY","data":{"foo":"BAR","0":1,"1":2,"2":3},"badge":"BADGE","icon":"https://icon.ico","image":"https://image.svg","lang":"en-GB","tag":"TAG","timestamp":1604141464,"vibrate":[300,10,200,10,500]}}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createMessageWithOptionsAndNewStructure(): void
    {
        $action = Action::create('A', 'T');
        $message = Message::create('TITLE', 'BODY')
            ->ltr()
            ->renotify()
            ->mute()
            ->interactionRequired()
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

        static::assertEquals('TITLE', $message->getTitle());
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
        static::assertEquals('ltr', $message->getDir());
        static::assertTrue($message->isSilent());
        static::assertTrue($message->getRenotify());
        static::assertTrue($message->isInteractionRequired());
        $expectedJson = '{"title":"TITLE","options":{"actions":[{"action":"A","title":"T"}],"body":"BODY","data":{"foo":"BAR","0":1,"1":2,"2":3},"dir":"ltr","badge":"BADGE","icon":"https://icon.ico","image":"https://image.svg","lang":"en-GB","renotify":true,"requireInteraction":true,"silent":true,"tag":"TAG","timestamp":1604141464,"vibrate":[300,10,200,10,500]}}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createMessageWithAutoDirection(): void
    {
        $message = Message::create('TITLE')
            ->auto()
        ;
        static::assertEquals('auto', $message->getDir());

        $expectedJson = '{"title":"TITLE","options":{"dir":"auto"}}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createMessageWithLTRDirection(): void
    {
        $message = Message::create('TITLE')
            ->ltr()
        ;
        static::assertEquals('ltr', $message->getDir());

        $expectedJson = '{"title":"TITLE","options":{"dir":"ltr"}}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createMessageWithRTLDirection(): void
    {
        $message = Message::create('TITLE')
            ->rtl()
        ;
        static::assertEquals('rtl', $message->getDir());

        $expectedJson = '{"title":"TITLE","options":{"dir":"rtl"}}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createMessageWithInteraction(): void
    {
        $message = Message::create('TITLE')
            ->interactionRequired()
        ;
        static::assertTrue($message->isInteractionRequired());

        $expectedJson = '{"title":"TITLE","options":{"requireInteraction":true}}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createMessageWithoutInteraction(): void
    {
        $message = Message::create('TITLE')
            ->noInteraction()
        ;
        static::assertFalse($message->isInteractionRequired());

        $expectedJson = '{"title":"TITLE","options":{"requireInteraction":false}}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createSilentMessage(): void
    {
        $message = Message::create('TITLE')
            ->mute()
        ;
        static::assertTrue($message->isSilent());

        $expectedJson = '{"title":"TITLE","options":{"silent":true}}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createNonSilentMessage(): void
    {
        $message = Message::create('TITLE')
            ->unmute()
        ;
        static::assertFalse($message->isSilent());

        $expectedJson = '{"title":"TITLE","options":{"silent":false}}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createWithRenotification(): void
    {
        $message = Message::create('TITLE')
            ->renotify()
        ;
        static::assertTrue($message->getRenotify());

        $expectedJson = '{"title":"TITLE","options":{"renotify":true}}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createWithoutRenotification(): void
    {
        $message = Message::create('TITLE')
            ->doNotRenotify()
        ;
        static::assertFalse($message->getRenotify());

        $expectedJson = '{"title":"TITLE","options":{"renotify":false}}';
        static::assertEquals($expectedJson, $message->toString());
        static::assertEquals($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
