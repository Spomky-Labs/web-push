<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use WebPush\Action;
use WebPush\Message;
use function json_encode;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * @internal
 */
final class MessageTest extends TestCase
{
    #[Test]
    public function createSimpleMessage(): void
    {
        $message = Message::create('TITLE');

        static::assertNull($message->getBody());
        static::assertSame('TITLE', $message->getTitle());
        static::assertNull($message->getTimestamp());
        static::assertNull($message->getTag());
        static::assertNull($message->getData());
        static::assertNull($message->getBadge());
        static::assertNull($message->getIcon());
        static::assertNull($message->getImage());
        static::assertNull($message->getLang());
        static::assertSame([], $message->getActions());
        static::assertNull($message->getVibrate());
        static::assertNull($message->getDir());
        static::assertNull($message->isSilent());
        static::assertNull($message->getRenotify());
        static::assertNull($message->isInteractionRequired());

        $expectedJson = '{"title":"TITLE","options":[]}';
        static::assertSame($expectedJson, $message->toString());
        static::assertSame($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    #[Test]
    public function createMessageWithOptionsAndOldStructure(): void
    {
        $action = Action::create('A', 'T');
        $message = Message::create('TITLE', 'BODY')
            ->withTag('TAG')
            ->withTimestamp(1_604_141_464)
            ->withLang('en-GB')
            ->withImage('https://image.svg')
            ->withBadge('BADGE')
            ->withIcon('https://icon.ico')
            ->withData([
                'foo' => 'BAR',
                1,
                2,
                3,
            ])
            ->addAction($action)
            ->vibrate(300, 10, 200, 10, 500)
        ;

        static::assertSame('BODY', $message->getBody());
        static::assertSame(1_604_141_464, $message->getTimestamp());
        static::assertSame('TAG', $message->getTag());
        static::assertSame([
            'foo' => 'BAR',
            1,
            2,
            3,
        ], $message->getData());
        static::assertSame('BADGE', $message->getBadge());
        static::assertSame('https://icon.ico', $message->getIcon());
        static::assertSame('https://image.svg', $message->getImage());
        static::assertSame('en-GB', $message->getLang());
        static::assertSame([$action], $message->getActions());
        static::assertSame([300, 10, 200, 10, 500], $message->getVibrate());
        static::assertNull($message->getDir());
        static::assertNull($message->isSilent());
        static::assertNull($message->getRenotify());
        static::assertNull($message->isInteractionRequired());

        $expectedJson = '{"title":"TITLE","options":{"actions":[{"action":"A","title":"T"}],"data":{"foo":"BAR","0":1,"1":2,"2":3},"badge":"BADGE","icon":"https://icon.ico","image":"https://image.svg","lang":"en-GB","tag":"TAG","timestamp":1604141464,"vibrate":[300,10,200,10,500],"body":"BODY"}}';
        static::assertSame($expectedJson, $message->toString());
        static::assertSame($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    #[Test]
    public function createMessageWithOptionsAndNewStructure(): void
    {
        $action = Action::create('A', 'T');
        $message = Message::create('TITLE', 'BODY')
            ->ltr()
            ->renotify()
            ->mute()
            ->interactionRequired()
            ->withTag('TAG')
            ->withTimestamp(1_604_141_464)
            ->withLang('en-GB')
            ->withImage('https://image.svg')
            ->withBadge('BADGE')
            ->withIcon('https://icon.ico')
            ->withData([
                'foo' => 'BAR',
                1,
                2,
                3,
            ])
            ->addAction($action)
            ->vibrate(300, 10, 200, 10, 500)
        ;

        static::assertSame('TITLE', $message->getTitle());
        static::assertSame('BODY', $message->getBody());
        static::assertSame(1_604_141_464, $message->getTimestamp());
        static::assertSame('TAG', $message->getTag());
        static::assertSame([
            'foo' => 'BAR',
            1,
            2,
            3,
        ], $message->getData());
        static::assertSame('BADGE', $message->getBadge());
        static::assertSame('https://icon.ico', $message->getIcon());
        static::assertSame('https://image.svg', $message->getImage());
        static::assertSame('en-GB', $message->getLang());
        static::assertSame([$action], $message->getActions());
        static::assertSame([300, 10, 200, 10, 500], $message->getVibrate());
        static::assertSame('ltr', $message->getDir());
        static::assertTrue($message->isSilent());
        static::assertTrue($message->getRenotify());
        static::assertTrue($message->isInteractionRequired());

        $expectedJson = '{"title":"TITLE","options":{"actions":[{"action":"A","title":"T"}],"data":{"foo":"BAR","0":1,"1":2,"2":3},"dir":"ltr","badge":"BADGE","icon":"https://icon.ico","image":"https://image.svg","lang":"en-GB","renotify":true,"requireInteraction":true,"silent":true,"tag":"TAG","timestamp":1604141464,"vibrate":[300,10,200,10,500],"body":"BODY"}}';
        static::assertSame($expectedJson, $message->toString());
        static::assertSame($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    #[Test]
    public function createMessageWithAutoDirection(): void
    {
        $message = Message::create('TITLE')
            ->auto()
        ;
        static::assertSame('auto', $message->getDir());

        $expectedJson = '{"title":"TITLE","options":{"dir":"auto"}}';
        static::assertSame($expectedJson, $message->toString());
        static::assertSame($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    #[Test]
    public function createMessageWithLTRDirection(): void
    {
        $message = Message::create('TITLE')
            ->ltr()
        ;
        static::assertSame('ltr', $message->getDir());

        $expectedJson = '{"title":"TITLE","options":{"dir":"ltr"}}';
        static::assertSame($expectedJson, $message->toString());
        static::assertSame($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    #[Test]
    public function createMessageWithRTLDirection(): void
    {
        $message = Message::create('TITLE')
            ->rtl()
        ;
        static::assertSame('rtl', $message->getDir());

        $expectedJson = '{"title":"TITLE","options":{"dir":"rtl"}}';
        static::assertSame($expectedJson, $message->toString());
        static::assertSame($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    #[Test]
    public function createMessageWithInteraction(): void
    {
        $message = Message::create('TITLE')
            ->interactionRequired()
        ;
        static::assertTrue($message->isInteractionRequired());

        $expectedJson = '{"title":"TITLE","options":{"requireInteraction":true}}';
        static::assertSame($expectedJson, $message->toString());
        static::assertSame($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    #[Test]
    public function createMessageWithoutInteraction(): void
    {
        $message = Message::create('TITLE')
            ->noInteraction()
        ;
        static::assertFalse($message->isInteractionRequired());

        $expectedJson = '{"title":"TITLE","options":{"requireInteraction":false}}';
        static::assertSame($expectedJson, $message->toString());
        static::assertSame($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    #[Test]
    public function createSilentMessage(): void
    {
        $message = Message::create('TITLE')
            ->mute()
        ;
        static::assertTrue($message->isSilent());

        $expectedJson = '{"title":"TITLE","options":{"silent":true}}';
        static::assertSame($expectedJson, $message->toString());
        static::assertSame($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    #[Test]
    public function createNonSilentMessage(): void
    {
        $message = Message::create('TITLE')
            ->unmute()
        ;
        static::assertFalse($message->isSilent());

        $expectedJson = '{"title":"TITLE","options":{"silent":false}}';
        static::assertSame($expectedJson, $message->toString());
        static::assertSame($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    #[Test]
    public function createWithRenotification(): void
    {
        $message = Message::create('TITLE')
            ->renotify()
        ;
        static::assertTrue($message->getRenotify());

        $expectedJson = '{"title":"TITLE","options":{"renotify":true}}';
        static::assertSame($expectedJson, $message->toString());
        static::assertSame($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    #[Test]
    public function createWithoutRenotification(): void
    {
        $message = Message::create('TITLE')
            ->doNotRenotify()
        ;
        static::assertFalse($message->getRenotify());

        $expectedJson = '{"title":"TITLE","options":{"renotify":false}}';
        static::assertSame($expectedJson, $message->toString());
        static::assertSame($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
