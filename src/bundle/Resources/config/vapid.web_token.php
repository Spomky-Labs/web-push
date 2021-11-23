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

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use WebPush\VAPID\JWSProvider;
use WebPush\VAPID\WebTokenProvider;

return static function (ContainerConfigurator $container): void {
    $container = $container->services()->defaults()
        ->private()
        ->autoconfigure()
        ->autowire()
    ;

    $container->set(JWSProvider::class)
        ->class(WebTokenProvider::class)
        ->args([
            '%webpush.vapid.web_token.public_key%',
            '%webpush.vapid.web_token.private_key%',
        ])
    ;
};
