<?php

declare(strict_types=1);

namespace WebPush;

use Psr\Log\LoggerInterface;

interface Loggable
{
    public function setLogger(LoggerInterface $logger): self;
}
