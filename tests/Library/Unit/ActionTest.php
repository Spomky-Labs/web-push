<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Unit;

use function json_encode;
use PHPUnit\Framework\TestCase;
use WebPush\Action;

/**
 * @internal
 * @group Unit
 * @group Library
 */
final class ActionTest extends TestCase
{
    /**
     * @test
     */
    public function createAction(): void
    {
        $action = Action::create('ACTION', '---TITLE---');

        static::assertEquals('ACTION', $action->getAction());
        static::assertEquals('---TITLE---', $action->getTitle());
        static::assertNull($action->getIcon());

        $expectedJson = '{"action":"ACTION","title":"---TITLE---"}';
        static::assertEquals($expectedJson, $action->toString());
        static::assertEquals($expectedJson, json_encode($action, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @test
     */
    public function createActionWithIcon(): void
    {
        $action = Action::create('ACTION', '---TITLE---')
            ->withIcon('https://icon.ico')
        ;

        static::assertEquals('ACTION', $action->getAction());
        static::assertEquals('---TITLE---', $action->getTitle());
        static::assertEquals('https://icon.ico', $action->getIcon());

        $expectedJson = '{"action":"ACTION","title":"---TITLE---","icon":"https://icon.ico"}';
        static::assertEquals($expectedJson, $action->toString());
        static::assertEquals($expectedJson, json_encode($action, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
