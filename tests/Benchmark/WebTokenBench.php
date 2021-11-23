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

namespace WebPush\Tests\Benchmark;

use WebPush\VAPID\JWSProvider;
use WebPush\VAPID\WebTokenProvider;

class WebTokenBench extends AbstractBench
{
    protected function jwtProvider(): JWSProvider
    {
        $publicKey = 'BNFEvAnv7SfVGz42xFvdcu-z-W_3FVm_yRSGbEVtxVRRXqCBYJtvngQ8ZN-9bzzamxLjpbw7vuHcHTT2H98LwLM';
        $privateKey = 'TcP5-SlbNbThgntDB7TQHXLslhaxav8Qqdd_Ar7VuNo';

        return WebTokenProvider::create($publicKey, $privateKey);
    }
}
