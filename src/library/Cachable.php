<?php

declare(strict_types=1);

namespace WebPush;

use Psr\Cache\CacheItemPoolInterface;

interface Cachable
{
    public function setCache(CacheItemPoolInterface $cache): self;
}
