<?php

declare(strict_types=1);

namespace WebPush\VAPID;

interface JWSProvider
{
    /**
     * @param array<string, mixed> $claims
     */
    public function computeHeader(array $claims): Header;
}
