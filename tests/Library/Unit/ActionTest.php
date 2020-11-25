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

        $expectedJson = '{"action":"ACTION","icon":"https://icon.ico","title":"---TITLE---"}';
        static::assertEquals($expectedJson, $action->toString());
        static::assertEquals($expectedJson, json_encode($action, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
