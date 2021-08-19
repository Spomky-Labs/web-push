<?php

declare(strict_types=1);

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
