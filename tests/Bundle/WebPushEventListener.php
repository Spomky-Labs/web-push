<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2020-2021 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace WebPush\Tests\Bundle;

use WebPush\StatusReport;

class WebPushEventListener
{
    /**
     * @var StatusReport[]
     */
    private array $events = [];

    public function __invoke(StatusReport $report): void
    {
        $this->events[] = $report;
    }

    /**
     * @return StatusReport[]
     */
    public function getEvents(): array
    {
        return $this->events;
    }
}
