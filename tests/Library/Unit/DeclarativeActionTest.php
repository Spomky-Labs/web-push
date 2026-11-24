<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use WebPush\DeclarativeAction;
use WebPush\Exception\ValidationException;
use function json_encode;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * @internal
 */
final class DeclarativeActionTest extends TestCase
{
    #[Test]
    public function createSimpleDeclarativeAction(): void
    {
        $action = DeclarativeAction::create('ACTION', 'TITLE', 'https://example.com/action');

        static::assertSame('ACTION', $action->getAction());
        static::assertSame('TITLE', $action->getTitle());
        static::assertSame('https://example.com/action', $action->getNavigate());
        static::assertNull($action->getIcon());

        $expectedJson = '{"action":"ACTION","title":"TITLE","navigate":"https://example.com/action"}';
        static::assertSame($expectedJson, $action->toString());
        static::assertSame($expectedJson, json_encode($action, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    #[Test]
    public function createDeclarativeActionWithIcon(): void
    {
        $action = DeclarativeAction::create('ACTION', 'TITLE', 'https://example.com/action')
            ->withIcon('https://example.com/icon.ico')
        ;

        static::assertSame('https://example.com/icon.ico', $action->getIcon());
        static::assertSame(
            '{"action":"ACTION","title":"TITLE","navigate":"https://example.com/action","icon":"https://example.com/icon.ico"}',
            $action->toString()
        );
    }

    /**
     * @param non-empty-string $expectedMessage
     */
    #[Test]
    #[DataProvider('invalidActions')]
    public function invalidActionsAreRejected(
        string $action,
        string $title,
        string $navigate,
        string $expectedMessage
    ): void {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($expectedMessage);

        DeclarativeAction::create($action, $title, $navigate);
    }

    /**
     * @return iterable<string, array{action: string, title: string, navigate: string, expectedMessage: string}>
     */
    public static function invalidActions(): iterable
    {
        yield 'empty action' => [
            'action' => ' ',
            'title' => 'TITLE',
            'navigate' => 'https://example.com/action',
            'expectedMessage' => 'The action shall not be empty.',
        ];
        yield 'empty title' => [
            'action' => 'ACTION',
            'title' => '',
            'navigate' => 'https://example.com/action',
            'expectedMessage' => 'The title shall not be empty.',
        ];
        yield 'empty navigation URL' => [
            'action' => 'ACTION',
            'title' => 'TITLE',
            'navigate' => '',
            'expectedMessage' => 'The navigation URL shall not be empty.',
        ];
    }
}
