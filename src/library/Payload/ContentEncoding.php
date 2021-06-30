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

namespace WebPush\Payload;

use Psr\Http\Message\RequestInterface;
use WebPush\SubscriptionInterface;

interface ContentEncoding
{
    public function encode(string $payload, RequestInterface $request, SubscriptionInterface $subscription): RequestInterface;

    public function name(): string;
}
