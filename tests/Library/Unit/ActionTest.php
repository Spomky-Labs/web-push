<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use function json_encode;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use PHPUnit\Framework\TestCase;
use WebPush\Action;

/**
 * @internal
 */
final class ActionTest extends TestCase
{
    /**
     * @test
     */
    public function createAction(): void
    {
        $action = Action::create('ACTION', '---TITLE---');

        static::assertSame('ACTION', $action->getAction());
        static::assertSame('---TITLE---', $action->getTitle());
        static::assertNull($action->getIcon());

        $expectedJson = '{"action":"ACTION","title":"---TITLE---"}';
        static::assertSame($expectedJson, $action->toString());
        static::assertSame($expectedJson, json_encode($action, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createActionWithIcon(): void
    {
        $action = Action::create('ACTION', '---TITLE---')
            ->withIcon('https://icon.ico')
        ;

        static::assertSame('ACTION', $action->getAction());
        static::assertSame('---TITLE---', $action->getTitle());
        static::assertSame('https://icon.ico', $action->getIcon());

        $expectedJson = '{"icon":"https://icon.ico","action":"ACTION","title":"---TITLE---"}';
        static::assertSame($expectedJson, $action->toString());
        static::assertSame(
            $expectedJson,
            json_encode($action, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }
}
