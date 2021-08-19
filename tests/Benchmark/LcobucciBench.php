<?php

declare(strict_types=1);

namespace WebPush\Tests\Benchmark;

use WebPush\VAPID\JWSProvider;
use WebPush\VAPID\LcobucciProvider;

class LcobucciBench extends AbstractBench
{
    protected function jwtProvider(): JWSProvider
    {
        $publicKey = 'BNFEvAnv7SfVGz42xFvdcu-z-W_3FVm_yRSGbEVtxVRRXqCBYJtvngQ8ZN-9bzzamxLjpbw7vuHcHTT2H98LwLM';
        $privateKey = 'TcP5-SlbNbThgntDB7TQHXLslhaxav8Qqdd_Ar7VuNo';

        return LcobucciProvider::create($publicKey, $privateKey);
    }
}
