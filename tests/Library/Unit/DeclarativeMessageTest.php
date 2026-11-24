<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use WebPush\DeclarativeAction;
use WebPush\DeclarativeMessage;
use WebPush\Exception\ValidationException;
use function json_encode;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * @internal
 */
final class DeclarativeMessageTest extends TestCase
{
    #[Test]
    public function createSimpleDeclarativeMessage(): void
    {
        $message = DeclarativeMessage::create('TITLE', 'https://example.com/page');

        static::assertSame('TITLE', $message->getTitle());
        static::assertSame('https://example.com/page', $message->getNavigate());
        static::assertNull($message->getBody());
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
        static::assertNull($message->isMutable());
        static::assertNull($message->getAppBadge());

        $expectedJson = '{"web_push":8030,"notification":{"title":"TITLE","navigate":"https://example.com/page"}}';
        static::assertSame($expectedJson, $message->toString());
        static::assertSame($expectedJson, json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    #[Test]
    public function createDeclarativeMessageFromTheWebkitBlogPost(): void
    {
        $message = DeclarativeMessage::create(
            'Webkit.org — Meet Declarative Web Push',
            'https://webkit.org/blog/16535/meet-declarative-web-push/'
        )
            ->withLang('en-US')
            ->ltr()
            ->withBody('Send push notifications without JavaScript or service worker!')
            ->unmute()
            ->withAppBadge(1)
        ;

        $expectedJson = '{"web_push":8030,"notification":{"title":"Webkit.org — Meet Declarative Web Push","navigate":"https://webkit.org/blog/16535/meet-declarative-web-push/","body":"Send push notifications without JavaScript or service worker!","dir":"ltr","lang":"en-US","silent":false},"app_badge":"1"}';
        static::assertSame($expectedJson, $message->toString());
    }

    #[Test]
    public function createDeclarativeMessageWithAllOptions(): void
    {
        $action = DeclarativeAction::create('A', 'T', 'https://example.com/action')->withIcon(
            'https://example.com/action.ico'
        );
        $message = DeclarativeMessage::create('TITLE', 'https://example.com/page', 'BODY')
            ->withTag('TAG')
            ->withTimestamp(1_604_141_464)
            ->withLang('en-GB')
            ->withImage('https://image.svg')
            ->withBadge('BADGE')
            ->withIcon('https://icon.ico')
            ->rtl()
            ->withData([
                'foo' => 'BAR',
            ])
            ->addAction($action)
            ->vibrate(300, 10, 200)
            ->renotify()
            ->interactionRequired()
            ->mute()
            ->mutable()
            ->withAppBadge(12)
        ;

        static::assertSame('BODY', $message->getBody());
        static::assertSame(1_604_141_464, $message->getTimestamp());
        static::assertSame('TAG', $message->getTag());
        static::assertSame('rtl', $message->getDir());
        static::assertSame([$action], $message->getActions());
        static::assertSame([300, 10, 200], $message->getVibrate());
        static::assertTrue($message->getRenotify());
        static::assertTrue($message->isInteractionRequired());
        static::assertTrue($message->isSilent());
        static::assertTrue($message->isMutable());
        static::assertSame(12, $message->getAppBadge());

        $expectedJson = '{"web_push":8030,"notification":{"title":"TITLE","navigate":"https://example.com/page","body":"BODY","dir":"rtl","lang":"en-GB","tag":"TAG","image":"https://image.svg","icon":"https://icon.ico","badge":"BADGE","vibrate":[300,10,200],"timestamp":1604141464,"renotify":true,"silent":true,"requireInteraction":true,"data":{"foo":"BAR"},"actions":[{"action":"A","title":"T","navigate":"https://example.com/action","icon":"https://example.com/action.ico"}]},"mutable":true,"app_badge":"12"}';
        static::assertSame($expectedJson, $message->toString());
    }

    #[Test]
    public function theDirectionAndTheFlagsCanBeChanged(): void
    {
        $message = DeclarativeMessage::create('TITLE', 'https://example.com/page')
            ->auto()
            ->doNotRenotify()
            ->noInteraction()
            ->unmute()
            ->immutable()
            ->withTitle('NEW TITLE')
            ->withNavigate('https://example.com/other')
            ->withBody('NEW BODY')
        ;

        static::assertSame('auto', $message->getDir());
        static::assertFalse($message->getRenotify());
        static::assertFalse($message->isInteractionRequired());
        static::assertFalse($message->isSilent());
        static::assertFalse($message->isMutable());
        static::assertSame('NEW TITLE', $message->getTitle());
        static::assertSame('https://example.com/other', $message->getNavigate());
        static::assertSame('NEW BODY', $message->getBody());

        $expectedJson = '{"web_push":8030,"notification":{"title":"NEW TITLE","navigate":"https://example.com/other","body":"NEW BODY","dir":"auto","renotify":false,"silent":false,"requireInteraction":false},"mutable":false}';
        static::assertSame($expectedJson, $message->toString());
    }

    #[Test]
    public function theTitleShallNotBeEmpty(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The title shall not be empty.');

        DeclarativeMessage::create(' ', 'https://example.com/page');
    }

    #[Test]
    public function theNavigationUrlShallNotBeEmpty(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The navigation URL shall not be empty.');

        DeclarativeMessage::create('TITLE', '');
    }

    #[Test]
    public function theApplicationBadgeShallNotBeNegative(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The application badge shall be a positive integer.');

        DeclarativeMessage::create('TITLE', 'https://example.com/page')->withAppBadge(-1);
    }
}
